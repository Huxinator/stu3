<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPost;

use KNPostingData;
use RPGPlot;
use RPGPlotMember;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;

final class AddKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_WRITE_KN';

    private $addKnPostRequest;

    public function __construct(
        AddKnPostRequestInterface $addKnPostRequest
    ) {
        $this->addKnPostRequest = $addKnPostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $title = $this->addKnPostRequest->getTitle();
        $text = $this->addKnPostRequest->getText();
        $plotid = $this->addKnPostRequest->getPlotId();
        $mark = $this->addKnPostRequest->getPostMark();

        if (mb_strlen($text) < 50) {
            $game->addInformation(_('Der Text ist zu kurz (mindestens 50 Zeichen)'));
            return;
        }

        $post = new KNPostingData();

        if ($plotid > 0) {
            $plot = RPGPlot::getById($plotid);
            if ($plot && RPGPlotMember::mayWriteStory($plot->getId(), $userId)) {
                $post->setPlotId($plot->getId());
                $post->setTitle($plot->getTitle());
            }
        } else {
            $post->setTitle($title);

            if (mb_strlen($title) < 10) {
                $game->addInformation(_('Der Titel ist zu kurz (mindestens 10 Zeichen)'));
                return;
            }
        }
        $post->setText($text);
        $post->setUserId($userId);
        $post->setDate(time());
        $post->save();

        $game->addInformation(_('Der Beitrag wurde hinzugefügt'));

        if ($mark) {
            $user->setKNMark($post->getId());
            $user->save();
        }

        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
