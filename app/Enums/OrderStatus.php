<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Shipping = 'shipping';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xác nhận',
            self::Confirmed => 'Đã xác nhận',
            self::Shipping => 'Đang giao hàng',
            self::Completed => 'Hoàn thành',
            self::Cancelled => 'Đã hủy',
        };
    }

    /** Lớp CSS Bootstrap cho badge trạng thái. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning text-dark',
            self::Confirmed => 'bg-primary',
            self::Shipping => 'bg-info text-dark',
            self::Completed => 'bg-success',
            self::Cancelled => 'bg-secondary',
        };
    }

    /**
     * Các trạng thái được phép chuyển tới từ trạng thái hiện tại.
     * Luồng: pending → confirmed → shipping → completed; hủy được khi chưa hoàn thành.
     *
     * @return list<self>
     */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Shipping, self::Cancelled],
            self::Shipping => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->nextStatuses(), true);
    }

    /** Khách hàng chỉ được tự hủy khi đơn còn chờ xác nhận. */
    public function isCancellableByCustomer(): bool
    {
        return $this === self::Pending;
    }
}
