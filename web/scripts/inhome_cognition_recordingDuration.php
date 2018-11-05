<?php
require_once 'duration_generator.class.php';

$begin_date = htmlspecialchars($_POST['from']);
$end_date = htmlspecialchars($_POST['to']);
$rank = htmlspecialchars($_POST['rank']);

$inhome_cognition_recording = new duration_generator('inhome_cognition_recording', $rank, $begin_date, $end_date);

$smin = htmlspecialchars($_POST['stage-dur-min']);
$smax = htmlspecialchars($_POST['stage-dur-max']);
$mmin = htmlspecialchars($_POST['module-dur-min']);
$mmax = htmlspecialchars($_POST['module-dur-max']);

if(
  is_numeric($smin) &&
  is_numeric($smax) &&
  is_numeric($mmin) &&
  is_numeric($mmax) &&
  $smax>$smin && $mmax>$mmin)
{
  $inhome_cognition_recording->set_par_time_range(array($smin,$smax,$mmin,$mmax));
}

$inhome_cognition_recording->set_standard_deviation_scale(1);
$inhome_cognition_recording->build_table_data();
echo $inhome_cognition_recording->build_table_html();