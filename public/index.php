<?php
// public/index.php

//http://localhost/anccemexProyecto/public/


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Router.php';

$router = new Router();
$router->route();





