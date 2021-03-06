<?php

namespace App;

use App\Reader\TxtReader;
use App\Exceptions\NotFileException;
use App\Exceptions\IncorrectExtensionException;
use App\Exceptions\NotEnoughDataPointsException;

class Sonar
{
    private array $data;
    private int $previousitem;
    private int $currentitem;

    public function __construct(
        private TxtReader $reader
    )
    {
        $this->data = $this->reader->read();
    }

    public function mergeDatapointsByCount(int $count): void
    {
        $data = [];
        foreach ($this->data as $key => $datapoint) {
            try {
                $newdatapoint = $this->sumPreviousNDatapoints($count, $key);
                $data[] = $newdatapoint;
            } catch (NotEnoughDataPointsException $e) {
                continue;
            }
        }
        $this->data = $data;
    }

    /**
     * @throws NotEnoughDataPointsException
     */
    public function sumPreviousNDatapoints(int $n, int $key): int
    {
        if ($key >= $n - 1) {
            return array_sum(array_slice($this->data, $key + 1 - $n, $n));
        } else {
            throw new NotEnoughDataPointsException('Not enough datapoints to sum.');
        }
    }

    public function countInclines(): int
    {
        return array_reduce($this->data, function($carry, $item) {
            $this->setCurrentitem($item);
            if ($this->isIncline()) $carry++;
            $this->setPreviousitem($item);
            return $carry;
        }, 0);
    }

    public function setCurrentitem(int $item): void
    {
        $this->currentitem = $item;
    }

    public function setPreviousitem(int $item): void
    {
        $this->previousitem = $item;
    }

    public function isIncline(): bool
    {
        if (isset($this->previousitem)) {
            return $this->isCurrentGreater();
        } else {
            return false;
        }
    }

    public function isCurrentGreater(): bool
    {
        return $this->currentitem > $this->previousitem;
    }
}