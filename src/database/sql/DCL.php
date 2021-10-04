<?php

namespace Thynkon\SimpleOrm\database\sql;

abstract class DCL
{
    const GRANT = 0;
    const REVOKE = 1;
    const COMMIT = 2;
    const ROLLBACK = 3;
    const SAVEPOINT = 4;
    const SET_TRANSACTION = 5;
}