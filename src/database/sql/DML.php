<?php

namespace Thynkon\SimpleOrm\database\sql;

abstract class DML
{
    const INSERT = 0;
    const UPDATE = 1;
    const DELETE = 2;
    const LOCK = 3;
    const CALL = 4;
    const EXPLAIN_PLAIN = 5;
}