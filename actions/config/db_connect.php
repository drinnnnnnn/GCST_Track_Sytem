<?php
// Compatibility shim for legacy action includes.
// This file allows existing action scripts to keep referencing actions/config/db_connect.php
// while the real connection logic is now centralized inside /database.

require_once __DIR__ . '/../../config/db_connect.php';
