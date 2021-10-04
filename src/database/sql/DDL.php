<?php

namespace Thynkon\SimpleOrm\database\sql;

abstract class DDL
{
    const CREATE = 0;
    const DROP = 1;
    const ALTER = 2;
    const TRUNCATE = 3;
    const COMMENT = 4;
    const RENAME = 5;
}