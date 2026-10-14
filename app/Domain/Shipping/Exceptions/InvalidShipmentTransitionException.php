<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Exceptions;

use LogicException;

final class InvalidShipmentTransitionException extends LogicException {}
