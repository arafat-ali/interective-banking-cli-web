<?php

declare(strict_types=1);
namespace App\Enum;

enum TransactionTypeEnum : string
{
    case DIPOSIT = 'DIPOSIT';
    case WITHDRAW = 'WITHDRAW';

    public static function fromValue(string $type): self
    {
        foreach (self::cases() as $item) {
            if( $type === $item->value ){
                return $item;
            }
        }
    }
}