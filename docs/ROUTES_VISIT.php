<?php
$routes->get('patient/intake', 'PatientController::intake');
$routes->post('patient/find', 'PatientController::find');
$routes->get('patient/provisional', 'PatientController::provisional');
