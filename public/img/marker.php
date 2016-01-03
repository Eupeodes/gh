<?php
\lib\Cache::permanent();
$marker = new \lib\Marker(filter_input_array(INPUT_GET));
$marker->show();