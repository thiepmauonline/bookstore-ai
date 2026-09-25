<?php

namespace App\Exceptions;

use DomainException;

/**
 * Lỗi nghiệp vụ đơn hàng; message được hiển thị trực tiếp cho người dùng.
 */
class OrderException extends DomainException {}
