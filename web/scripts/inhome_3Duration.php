<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$inhome_3 = new duration_generator('inhome_3', $rank, $begin_date, $end_date);
$inhome_3->set_threshold(100);
$inhome_3->set_standard_deviation_scale(1);
$inhome_3->build_table_data();
echo $inhome_3->build_table_html();