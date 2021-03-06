<?php
require_once 'tonometer_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);
$par_min = htmlspecialchars($_POST['par-qac-min']);
$par_max = htmlspecialchars($_POST['par-qac-max']);

$tonometer = new tonometer_generator('tonometer', $rank, $begin_date, $end_date);
$tonometer->build_table_data();
echo $tonometer->build_table_html();
