<?php

namespace App\Observers;

use Bschmitt\Amqp\Facades\Amqp;
use Bschmitt\Amqp\Message;
use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * Handle the model "created" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function created(Model $model)
    {
        try {
            $this->publish("{$model->getTable()}." . __FUNCTION__, $model->toArray());
        } catch (\Exception $exception) {
            $this->report([
                'id' => $model->id,
                'table' => $model->getTable(),
                'action' => __FUNCTION__,
                'exception' => $exception
            ]);
        }
    }

    /**
     * Handle the model "updated" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function updated(Model $model)
    {
        try {
            $this->publish("{$model->getTable()}." . __FUNCTION__, $model->toArray());
        } catch (\Exception $exception) {
            $this->report([
                'id' => $model->id,
                'table' => $model->getTable(),
                'action' => __FUNCTION__,
                'exception' => $exception
            ]);
        }
    }

    /**
     * Handle the model "deleted" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleted(Model $model)
    {
        try {
            $this->publish("{$model->getTable()}." . __FUNCTION__, $model->toArray());
        } catch (\Exception $exception) {
            $this->report([
                'id' => $model->id,
                'table' => $model->getTable(),
                'action' => __FUNCTION__,
                'exception' => $exception
            ]);
        }
    }

    protected function publish(
        string $routingKey = null,
        array $data = []
    ) {
        $message = new Message(
            json_encode($data),
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2 //persistent
            ]
        );

        Amqp::publish(
            $routingKey,
            $message,
            [
                'exchange' => config('amqp.properties.production.exchange'),
                'exchange_type' => config('amqp.properties.production.exchange_type'),
            ]
        );
    }

    protected function report(array $params)
    {
        list('id' => $id, 'table' => $table, 'action' => $action, 'exception' => $exception) = $params;

        $myException = new \Exception(
            "Erro ao sincronizar dados com o RABBITMQ. Tabela:{$table} id: {$id}, action:{$action}",
            0,
            $exception
        );

        report($myException);
    }
}
