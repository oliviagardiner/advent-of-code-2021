<?php

namespace App\InputReader;

interface InputReaderInterface
{
    public function readLines(): array;

    public function mapLinesToInteger(): array;
}