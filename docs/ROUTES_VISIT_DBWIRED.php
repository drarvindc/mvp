<?php
$routes->get('patient/intake', 'PatientController::intake');
$routes->post('patient/find', 'PatientController::find');
$routes->get('patient/provisional', 'PatientController::provisional');
$routes->get('patient/print-existing', 'PatientController::printExisting');
$routes->post('patient/provisional/create', 'PatientController::provisionalCreate');
