<?php

declare(strict_types=1);

namespace Am2tec\EvolutionSdk\Facades;

use Am2tec\EvolutionSdk\EvolutionClient;
use Am2tec\EvolutionSdk\Resources\ChatResource;
use Am2tec\EvolutionSdk\Resources\GroupResource;
use Am2tec\EvolutionSdk\Resources\InstanceResource;
use Am2tec\EvolutionSdk\Resources\MessageResource;
use Am2tec\EvolutionSdk\Resources\WebhookResource;
use Illuminate\Support\Facades\Facade;

/**
 * @method static InstanceResource instance()
 * @method static MessageResource message()
 * @method static ChatResource chat()
 * @method static GroupResource group()
 * @method static WebhookResource webhook()
 *
 * @see EvolutionClient
 */
class Evolution extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'evolution';
    }
}
