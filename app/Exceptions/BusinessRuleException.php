<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Vi phạm quy tắc nghiệp vụ (tồn kho, thanh toán, trạng thái đơn, …).
 */
class BusinessRuleException extends \RuntimeException
{
}
