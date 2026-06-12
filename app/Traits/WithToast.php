<?php

namespace App\Traits;

trait WithToast
{
    public function toastSuccess(string $message, string $title = 'Success'): void
    {
        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => $message,
            'title'   => $title,
        ]);
    }

    public function toastError(string $message, string $title = 'Error'): void
    {
        $this->dispatch('toast', [
            'type'    => 'error',
            'message' => $message,
            'title'   => $title,
        ]);
    }

    public function toastWarning(string $message, string $title = 'Warning'): void
    {
        $this->dispatch('toast', [
            'type'    => 'warning',
            'message' => $message,
            'title'   => $title,
        ]);
    }

    public function toastInfo(string $message, string $title = 'Info'): void
    {
        $this->dispatch('toast', [
            'type'    => 'info',
            'message' => $message,
            'title'   => $title,
        ]);
    }
}