<?php

namespace Stu\Module\Control;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Exception\AccessViolation;
use Stu\Exception\EntityLockedException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\SanityCheckException;
use Stu\Exception\SessionInvalidException;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\TickGameStateException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\Render\GameTalRendererInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\GameConfigInterface;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Ubench;

final class GameController implements GameControllerInterface
{
    /** @var string */
    public const DEFAULT_VIEW = 'DEFAULT_VIEW';

    /** @var string */
    private const GAME_VERSION_DEV = 'dev';

    private SessionInterface $session;

    private SessionStringRepositoryInterface $sessionStringRepository;

    private TalPageInterface $talPage;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private StuConfigInterface $stuConfig;

    private GameTurnRepositoryInterface $gameTurnRepository;

    private GameConfigRepositoryInterface $gameConfigRepository;

    private EntityManagerInterface $entityManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    private Ubench $benchmark;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private GameRequestRepositoryInterface $gameRequestRepository;

    private LoggerUtilInterface $loggerUtil;

    private GameTalRendererInterface $gameTalRenderer;

    private UuidGeneratorInterface $uuidGenerator;

    private GameRequestSaverInterface $gameRequestSaver;

    private EventDispatcherInterface $eventDispatcher;

    /** @var array<Notification> */
    private array $gameInformations = [];

    /** @var array<string, string> */
    private array $siteNavigation = [];

    private string $pagetitle = '';

    private string $macro = '';

    /** @var array<string> */
    private array $execjs = [];

    private ?GameTurnInterface $currentRound = null;

    /** @var array<string> */
    private array $achievements = [];

    /** @var array<string, mixed> $viewContext */
    private array $viewContext = [];

    private ?array $gameStats = null;

    private string $loginError = '';

    /** @var array<int, resource> */
    private array $semaphores = [];

    /** @var array<int, GameConfigInterface> */
    private ?array $gameConfig = null;

    private ?GameRequestInterface $gameRequest = null;

    public function __construct(
        SessionInterface $session,
        SessionStringRepositoryInterface $sessionStringRepository,
        TalPageInterface $talPage,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        StuConfigInterface $stuConfig,
        GameTurnRepositoryInterface $gameTurnRepository,
        GameConfigRepositoryInterface $gameConfigRepository,
        EntityManagerInterface $entityManager,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository,
        Ubench $benchmark,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        GameRequestRepositoryInterface $gameRequestRepository,
        GameTalRendererInterface $gameTalRenderer,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        UuidGeneratorInterface $uuidGenerator,
        EventDispatcherInterface $eventDispatcher,
        GameRequestSaverInterface $gameRequestSaver
    ) {
        $this->session = $session;
        $this->sessionStringRepository = $sessionStringRepository;
        $this->talPage = $talPage;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->stuConfig = $stuConfig;
        $this->gameTurnRepository = $gameTurnRepository;
        $this->gameConfigRepository = $gameConfigRepository;
        $this->entityManager = $entityManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
        $this->benchmark = $benchmark;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->gameRequestRepository = $gameRequestRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->gameTalRenderer = $gameTalRenderer;
        $this->uuidGenerator = $uuidGenerator;
        $this->gameRequestSaver = $gameRequestSaver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array<string, mixed> $viewContext
     */
    public function setView(string $view, array $viewContext = []): void
    {
        request::setVar($view, 1);

        $this->viewContext = $viewContext;
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewContext(): array
    {
        return $this->viewContext;
    }

    public function getGameState(): int
    {
        return $this->getGameConfig()[GameEnum::CONFIG_GAMESTATE]->getValue();
    }

    public function setTemplateFile(string $tpl): void
    {
        $this->talPage->setTemplate($tpl);
    }

    public function setMacroAndTemplate($macro, string $tpl): void
    {
        $this->macro = $macro;
        $this->setTemplateFile($tpl);
    }

    public function setMacroInAjaxWindow($macro): void
    {
        $this->macro = $macro;
        $this->setTemplateFile('html/ajaxwindow.xhtml');
    }

    public function showMacro($macro): void
    {
        $this->macro = $macro;
        $this->setTemplateFile('html/ajaxempty.xhtml');
    }

    public function getMacro(): string
    {
        return $this->macro;
    }

    public function addInformationfWithLink(string $text, string $link, ...$args): void
    {
        $this->addInformationWithLink(vsprintf(
            $text,
            $args
        ), $link);
    }

    public function addInformationf(string $text, ...$args): void
    {
        $this->addInformation(vsprintf(
            $text,
            $args
        ));
    }

    public function addInformationWithLink(string $msg, string $link, bool $override = false): void
    {
        if ($override) {
            $this->gameInformations = array();
        }
        $notification = new Notification();
        $notification->setText($msg);
        $notification->setLink($link);

        $this->gameInformations[] = $notification;
    }

    public function addInformation(string $msg, bool $override = false): void
    {
        if ($override) {
            $this->gameInformations = array();
        }
        $notification = new Notification();
        $notification->setText($msg);

        $this->gameInformations[] = $notification;
    }

    public function addInformationMerge(array $info): void
    {
        $notificationArray = [];
        foreach ($info as $value) {
            $notification = new Notification();
            $notification->setText($value);
            $notificationArray[] = $notification;
        }

        $this->gameInformations = array_merge($notificationArray, $this->getInformation());
    }

    public function addInformationMergeDown(array $info): void
    {
        $notificationArray = array();
        foreach ($info as $value) {
            $notification = new Notification();
            $notification->setText($value);
            $notificationArray[] = $notification;
        }

        $this->gameInformations = array_merge($this->getInformation(), $notificationArray);
    }

    public function getInformation(): array
    {
        return $this->gameInformations;
    }

    public function sendInformation(
        $recipient_id,
        $sender_id = GameEnum::USER_NOONE,
        $category_id = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        ?string $href = null
    ): void {

        if ($sender_id === $recipient_id) {
            return;
        }

        $textOnlyArray = array();
        foreach ($this->getInformation() as $value) {
            $textOnlyArray[] = $value->getText();
        }

        $this->privateMessageSender->send(
            (int) $sender_id,
            (int) $recipient_id,
            join('<br />', $textOnlyArray),
            $category_id,
            $href
        );
    }

    public function setTemplateVar(string $key, $variable): void
    {
        $this->talPage->setVar($key, $variable);
    }

    public function getUser(): UserInterface
    {
        $user = $this->session->getUser();

        if ($user === null) {
            throw new AccessViolation('User not set');
        }
        return $user;
    }

    public function hasUser(): bool
    {
        return $this->session->getUser() !== null;
    }

    /**
     * @return array<int, GameConfigInterface>
     */
    public function getGameConfig(): array
    {
        if ($this->gameConfig === null) {
            $this->gameConfig = [];

            foreach ($this->gameConfigRepository->findAll() as $item) {
                $this->gameConfig[$item->getOption()] = $item;
            }
        }
        return $this->gameConfig;
    }

    public function getUniqId(): string
    {
        return uniqid();
    }

    public function setNavigation(
        array $navigationItems
    ): GameControllerInterface {
        foreach ($navigationItems as $item) {
            $this->appendNavigationPart($item['url'], $item['title']);
        }

        return $this;
    }

    public function appendNavigationPart(
        string $url,
        string $title
    ): void {
        $this->siteNavigation[$url] = $title;
    }

    public function getNavigation(): array
    {
        return $this->siteNavigation;
    }

    public function getPageTitle(): string
    {
        return $this->pagetitle;
    }

    public function setPageTitle(string $title): void
    {
        $this->pagetitle = $title;
    }

    public function getExecuteJS(): array
    {
        return $this->execjs;
    }

    public function addExecuteJS(string $value): void
    {
        $this->execjs[] = $value;
    }

    public function redirectTo(string $href): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
        header('Location: ' . $href);
        exit;
    }

    public function getCurrentRound(): GameTurnInterface
    {
        if ($this->currentRound === null) {
            $this->currentRound = $this->gameTurnRepository->getCurrent();
        }
        return $this->currentRound;
    }

    public function getJavascriptPath(): string
    {
        $gameVersion = $this->stuConfig->getGameSettings()->getVersion();
        if ($gameVersion === self::GAME_VERSION_DEV) {
            return '/static';
        }

        return sprintf(
            '/version_%s/static',
            $gameVersion
        );
    }

    public function checkDatabaseItem($databaseEntryId): void
    {
        $userId = $this->getUser()->getId();

        if ($databaseEntryId > 0 && $this->databaseUserRepository->exists($userId, $databaseEntryId) === false) {
            $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($this->getUser(), $databaseEntryId);

            if ($entry !== null) {
                $this->achievements[] = sprintf(
                    _('Neuer Datenbankeintrag: %s (+%d Punkte)'),
                    $entry->getDescription(),
                    $entry->getCategory()->getPoints()
                );
            }
        }
    }

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function getSessionString(): string
    {
        $string = bin2hex(random_bytes(15));

        $sessionStringEntry = $this->sessionStringRepository->prototype();
        $sessionStringEntry->setUser($this->getUser());
        $sessionStringEntry->setDate(new DateTimeImmutable());
        $sessionStringEntry->setSessionString($string);

        $this->sessionStringRepository->save($sessionStringEntry);

        return $string;
    }

    public function sessionAndAdminCheck(): void
    {
        $this->session->createSession(true);

        if (!$this->isAdmin()) {
            header('Location: /');
        }
    }

    public function getGameRequestId(): string
    {
        return $this->getGameRequest()->getRequestId();
    }

    public function getGameRequest(): GameRequestInterface
    {
        if ($this->gameRequest === null) {
            $gameRequest = $this->gameRequestRepository->prototype();
            $gameRequest->setTime(time());
            $gameRequest->setTurnId($this->getCurrentRound());
            $gameRequest->setParameterArray(request::isPost() ? request::postvars() : request::getvars());
            $gameRequest->setRequestId($this->uuidGenerator->genV4());

            if ($this->hasUser()) {
                $gameRequest->setUserId($this->getUser());
            }

            $this->gameRequest = $gameRequest;
        }
        return $this->gameRequest;
    }

    public function main(
        string $module,
        array $actions,
        array $views,
        bool $session_check = true,
        bool $admin_check = false
    ): void {
        $gameRequest = $this->getGameRequest();
        $gameRequest->setModule($module);

        try {
            $this->session->createSession($session_check);

            if ($session_check === false) {
                $this->session->checkLoginCookie();
            }

            if (
                $admin_check === true
                && $this->isAdmin() === false
            ) {
                header('Location: /');
            }

            $this->checkUserAndGameState($gameRequest);

            // log action & view and time they took
            $startTime = hrtime(true);
            try {
                $this->executeCallback($actions, $gameRequest);
            } catch (SanityCheckException $e) {
                $gameRequest->addError($e);
            } catch (EntityLockedException $e) {
                $this->addInformation($e->getMessage());
            }
            $actionMs = hrtime(true) - $startTime;

            $startTime = hrtime(true);
            try {
                $this->executeView($views, $gameRequest);
            } catch (SanityCheckException $e) {
                $gameRequest->addError($e);
            } catch (EntityLockedException $e) {
                $this->addInformation($e->getMessage());
            }
            $viewMs = hrtime(true) - $startTime;

            $gameRequest->setActionMs((int)$actionMs / 1000000);
            $gameRequest->setViewMs((int)$viewMs / 1000000);
        } catch (SessionInvalidException $e) {
            session_destroy();

            if (request::isAjaxRequest()) {
                header('HTTP/1.0 400');
            } else {
                header('Location: /');
            }
            return;
        } catch (LoginException $e) {
            $this->loginError = $e->getMessage(); //TODO kann weg?

            $this->setTemplateFile('html/accountlocked.xhtml');
            $this->talPage->setVar('THIS', $this);
            $this->talPage->setVar('REASON', $e->getDetails());
        } catch (AccountNotVerifiedException $e) {
            $this->setTemplateFile('html/smsverification.xhtml');
            $this->talPage->setVar('THIS', $this);
            if ($e->getMessage() !== '') {
                $this->talPage->setVar('REASON', $e->getMessage());
            }
        } catch (TickGameStateException $e) {
            $this->setPageTitle(_('Rundenwechsel aktiv'));
            $this->setTemplateFile('html/tick.xhtml');

            $this->talPage->setVar('THIS', $this);
        } catch (MaintenanceGameStateException $e) {
            $this->setPageTitle(_('Wartungsmodus'));
            $this->setTemplateFile('html/maintenance.xhtml');

            $this->talPage->setVar('THIS', $this);
        } catch (RelocationGameStateException $e) {
            $this->setPageTitle(_('Umzugsmodus'));
            $this->setTemplateFile('html/relocation.xhtml');

            $this->talPage->setVar('THIS', $this);
        } catch (ShipDoesNotExistException $e) {
            $this->addInformation(_('Dieses Schiff existiert nicht!'));
            $this->setTemplateFile('html/ship.xhtml');
        } catch (ShipIsDestroyedException $e) {
            $this->addInformation('Dieses Schiff wurde zerstört!');
            $this->setTemplateFile('html/ship.xhtml');
        } catch (ItemNotFoundException $e) {
            $this->addInformation('Das angeforderte Item wurde nicht gefunden');
            $this->setTemplateFile('html/notfound.xhtml');
        } catch (UnallowedUplinkOperation $e) {
            $this->addInformation('Diese Aktion ist per Uplink nicht möglich!');

            if (request::isAjaxRequest()) {
                $this->setMacroInAjaxWindow('html/sitemacros.xhtml/systeminformation');
            } else {
                $this->setTemplateFile('html/ship.xhtml');
            }
        } catch (\Throwable $e) {
            throw $e;
        }

        if (!$this->talPage->isTemplateSet()) {
            $this->loggerUtil->init('tal', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log(sprintf('NO TEMPLATE FILE SPECIFIED, Method: %s', request::isPost() ? 'POST' : 'GET'));
            $this->loggerUtil->log(print_r(request::isPost() ? request::postvars() : request::getvars(), true));
            $this->loggerUtil->init('stu');
        }

        $user = $this->hasUser()
            ? $this->getUser()
            : null;

        // RENDER!
        $startTime = hrtime(true);

        $renderResult = $this->gameTalRenderer->render($this, $user, $this->talPage);

        $renderMs = hrtime(true) - $startTime;

        ob_start();
        echo $renderResult;
        ob_end_flush();

        // SAVE META DATA
        $gameRequest->setRenderMs((int)$renderMs / 1000000);

        $this->gameRequestSaver->save($gameRequest);
    }

    private function checkUserAndGameState(GameRequestInterface $gameRequest): void
    {
        if ($this->hasUser()) {
            $gameRequest->setUserId($this->getUser());

            if ($this->getUser()->isLocked()) {
                $userLock = $this->getUser()->getUserLock();
                $this->session->logout();

                throw new LoginException(
                    _('Dein Spieleraccount wurde gesperrt'),
                    sprintf(_('Dein Spieleraccount ist noch für %d Ticks gesperrt. Begründung: %s'), $userLock->getRemainingTicks(), $userLock->getReason())
                );
            }
            if (!request::postString('smscode') && $this->getUser()->getState() === UserEnum::USER_STATE_SMS_VERIFICATION) {
                throw new AccountNotVerifiedException();
            }
            $gameState = $this->getGameState();
            if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_TICK) {
                throw new TickGameStateException();
            }

            if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE && !$this->isAdmin()) {
                throw new MaintenanceGameStateException();
            }

            if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_RELOCATION) {
                throw new RelocationGameStateException();
            }
        }
    }

    public function isAdmin(): bool
    {
        if ($this->hasUser() === false) {
            return false;
        }

        return in_array(
            $this->getUser()->getId(),
            $this->stuConfig->getGameSettings()->getAdminIds(),
            true
        );
    }

    public function isSemaphoreAlreadyAcquired(int $key): bool
    {
        return array_key_exists($key, $this->semaphores);
    }

    public function addSemaphore(int $key, $semaphore): void
    {
        $this->semaphores[$key] = $semaphore;
    }

    /**
     * Triggers a certain event
     */
    public function triggerEvent(object $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * @param array<string, ActionControllerInterface> $actions
     */
    private function executeCallback(array $actions, GameRequestInterface $gameRequest): void
    {
        foreach ($actions as $actionIdentifier => $actionController) {
            if (request::indString($actionIdentifier)) {

                $gameRequest->setAction($actionIdentifier);

                if ($actionController->performSessionCheck() === true && !request::isPost()) {
                    if (!$this->sessionStringRepository->isValid(
                        (string)request::indString('sstr'),
                        $this->getUser()->getId()
                    )) {
                        return;
                    }
                }
                $actionController->handle($this);

                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param array<string, ViewControllerInterface> $views
     */
    private function executeView(array $views, GameRequestInterface $gameRequest): void
    {
        foreach ($views as $viewIdentifier => $config) {

            if (request::indString($viewIdentifier)) {
                $gameRequest->setView($viewIdentifier);

                $config->handle($this);
                $this->entityManager->flush();

                return;
            }
        }

        $view = $views[static::DEFAULT_VIEW] ?? null;

        if ($view !== null) {
            $view->handle($this);
            $this->entityManager->flush();
        }
    }

    public function getGameStats(): array
    {
        if ($this->gameStats === null) {
            $this->gameStats = [
                'turn' => $this->getCurrentRound(),
                'player' => $this->userRepository->getActiveAmount(),
                'playeronline' => $this->userRepository->getActiveAmountRecentlyOnline(time() - 300),
            ];
        }
        return $this->gameStats;
    }

    public function getGameStateTextual(): string
    {
        return GameEnum::gameStateTypeToDescription($this->getGameState());
    }

    public function getLoginError(): string
    {
        return $this->loginError;
    }

    public function getBenchmarkResult(): array
    {
        $this->loggerUtil->log(sprintf('getBenchmarkResult, timestamp: %F', microtime(true)));
        $this->benchmark->end();

        return [
            'executionTime' => $this->benchmark->getTime(),
            'memoryUsage' => $this->benchmark->getMemoryUsage(),
            'memoryPeakUsage' => $this->benchmark->getMemoryPeak()
        ];
    }

    public function getContactlistModes(): array
    {
        return [
            ContactListModeEnum::CONTACT_FRIEND => [
                'mode' => ContactListModeEnum::CONTACT_FRIEND, 'name' => _('Freund')
            ],
            ContactListModeEnum::CONTACT_ENEMY => [
                'mode' => ContactListModeEnum::CONTACT_ENEMY, 'name' => _('Feind')
            ],
            ContactListModeEnum::CONTACT_NEUTRAL => [
                'mode' => ContactListModeEnum::CONTACT_NEUTRAL, 'name' => _('Neutral')
            ],
        ];
    }
}
