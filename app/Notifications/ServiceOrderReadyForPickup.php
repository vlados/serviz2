<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceOrderReadyForPickup extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ServiceOrder $serviceOrder
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $scooterModel = $this->serviceOrder->scooter->model;
        
        return (new MailMessage)
            ->subject('Вашата тротинетка е готова за вземане')
            ->greeting('Здравейте, ' . $notifiable->name . '!')
            ->line('Вашата тротинетка ' . $scooterModel . ' е готова за вземане от нашия сервиз.')
            ->line('Подробности за поръчката:')
            ->line('Номер на поръчка: ' . $this->serviceOrder->order_number)
            ->line('Извършена работа: ' . strip_tags($this->serviceOrder->work_performed))
            ->line('Обща сума за плащане: ' . number_format($this->serviceOrder->price, 2) . ' лв.')
            ->action('Свържете се с нас', url('/'))
            ->line('Очакваме ви да вземете и платите вашата тротинетка.')
            ->line('Благодарим ви, че избрахте нашия сервиз!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'service_order_id' => $this->serviceOrder->id,
            'order_number' => $this->serviceOrder->order_number,
            'scooter_model' => $this->serviceOrder->scooter->model,
            'completed_at' => $this->serviceOrder->completed_at,
        ];
    }
    
    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param object $notifiable
     * @return string
     */
    public function toVonage(object $notifiable): string
    {
        $scooterModel = $this->serviceOrder->scooter->model;
        $orderNumber = $this->serviceOrder->order_number;
        $price = number_format($this->serviceOrder->price, 2);
        
        return "Здравейте, {$notifiable->name}! Вашата тротинетка {$scooterModel} е готова за вземане. Номер на поръчка: {$orderNumber}. Обща сума: {$price} лв.";
    }
}