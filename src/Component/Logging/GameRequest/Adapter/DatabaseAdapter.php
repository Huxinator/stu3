<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Monolog\Level;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameRequestInterface;

/**
 * Adapter for saving game requests in the database
 *
 * @deprecated Use LogfileAdapter
 */
final class DatabaseAdapter extends AbstractAdapter
{
    private Connection $database;

    public function __construct(
        Connection $database
    ) {
        $this->database = $database;
    }

    protected function log(
        GameRequestInterface $gameRequest,
        Level $logLevel
    ): void {
        $params = $gameRequest->getParameterArray();
        $params['request_id'] = $gameRequest->getRequestId();
        $params['log_type'] = $logLevel;

        /**
         * We do not perform transaction handling in here
         * If an error occurs, the app should end the transaction before saving the error game state
         */
        $this->database->insert(
            GameRequest::TABLE_NAME,
            [
                'turn_id' => $gameRequest->getTurnId(),
                'time' => $gameRequest->getTime(),
                'module' => $gameRequest->getModule(),
                'action' => $gameRequest->getAction(),
                'action_ms' => $gameRequest->getActionMs(),
                'view' => $gameRequest->getView(),
                'view_ms' => $gameRequest->getViewMs(),
                'user_id' => (int) $gameRequest->getUserId(),
                'render_ms' => $gameRequest->getRenderMs(),
                'params' => json_encode($params, JSON_PRETTY_PRINT),
            ],
            [
                'turn_id' => ParameterType::INTEGER,
                'time' => ParameterType::INTEGER,
                'module' => ParameterType::STRING,
                'action' => ParameterType::STRING,
                'action_ms' => ParameterType::INTEGER,
                'view' => ParameterType::STRING,
                'view_ms' => ParameterType::INTEGER,
                'user_id' => ParameterType::INTEGER,
                'render_ms' => ParameterType::INTEGER,
                'params' => ParameterType::STRING,
            ]
        );
    }
}