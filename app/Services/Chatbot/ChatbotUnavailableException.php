<?php

namespace App\Services\Chatbot;

use RuntimeException;

/** Dịch vụ AI không dùng được (chưa cấu hình key, hết hạn mức, lỗi mạng...). */
class ChatbotUnavailableException extends RuntimeException {}
