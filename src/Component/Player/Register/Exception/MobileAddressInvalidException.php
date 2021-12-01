<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register\Exception;

use Stu\Component\ErrorHandling\ErrorCodeEnum;

final class MobileAddressInvalidException extends RegistrationException
{
    protected $code = ErrorCodeEnum::MOBILE_ADDRESS_INVALID;

    protected $message = 'The provided mobile address is not valid';
}