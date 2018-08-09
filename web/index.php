<?php
require_once (dirname(__FILE__).'/../settings.ini.php');
require_once $SETTINGS['path']['APPLICATION'].'/web/scripts/common.php';

$min_rank=1;
$max_rank=3;
$sql =
  'select '.
  'min(start_date) as min_date, '.
  'max(start_date) as max_date, '.
  'rank '.
  'from interview '.
  'group by rank';
$date_ranges = json_encode($db->get_all( $sql ));
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>CLSA-&Eacute;LCV QAC</title>
    <link rel="stylesheet" type="text/css" href="css/qac.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
      var stat_stages =[
        'scripts/bloodTable.php',
        'scripts/osipvTable.php',
        'scripts/social_networkTable.php',
        'scripts/contraindicationsTable.php',
        'scripts/urineTable.php',
        'scripts/time_based_pmtTable.php',
        'scripts/event_pmtTable.php',
        'scripts/general_healthTable.php',
        'scripts/functional_statusTable.php',
        'scripts/retinal_scanTable.php',
        'scripts/lateral_bone_densityTable.php',
        'scripts/cognitive_testTable.php',
        'scripts/carotid_intima_stillTable.php',
        'scripts/whole_body_bone_densityTable.php',
        'scripts/forearm_bone_densityTable.php',
        'scripts/ecgTable.php',
        'scripts/carotid_intima_cineloopTable.php',
        'scripts/dual_hip_bone_densityTable.php',
        'scripts/carotid_intima_srTable.php',
        'scripts/spine_bone_densityTable.php'];
      var date_json = <?php echo $date_ranges; ?>;
      var date_ranges = [];
      date_json.forEach(function(obj){
        date_ranges[obj.rank]={'min_date':obj.min_date,'max_date':obj.max_date};
      });
      $(function(){
        $('#stage').change( function() {
          $('#qacform').attr('action', $(this).val());
          console.log('changing action to : '+$(this).val());
          if(stat_stages.indexOf($(this).val()) > -1) {
            $('#stat-option').prop('disabled',false).change();
          } else {
            $('#stat-option').prop('disabled',true).change();
          }
        }).change();

        $('#from').datepicker({
          dateFormat: 'yy-mm-dd',
          changeMonth: true,
          changeYear: true,
          showAnim: 'blind',
          onSelect: function(dateText, inst){
            var newDate = new Date(dateText);
            $('#to').datepicker('option','minDate', newDate);
          }
        });

        $('#to').datepicker({
          dateFormat: 'yy-mm-dd',
          changeMonth: true,
          changeYear: true,
          showAnim: 'blind',
          onSelect: function(dateText, inst){
            var newDate = new Date(dateText);
            $('#from').datepicker('option','maxDate', newDate);
          }
        });

        $('#rank').bind('keyup change', function(e) {
          var num = $(this).val();
          console.log('new wave: '+num);
          $('#to, #from').datepicker('setDate',null);
          $('#to, #from').datepicker('option','minDate',date_ranges[num].min_date);
          $('#to, #from').datepicker('option','maxDate',date_ranges[num].max_date);
          $('#from').datepicker('option','defaultDate',date_ranges[num].min_date);
          $('#to').datepicker('option','defaultDate',date_ranges[num].max_date);
          if(3==num) {
            $('#stage option[value="scripts/functional_statusTable.php"]').hide();
            $('#stage option[value="scripts/social_networkTable.php"]').show();
            $('#stage option[value="scripts/general_healthTable.php"]').show();
            $('#stage option[value="scripts/spine_bone_densityTable.php"]').show();
            $('#stage option[value="scripts/sitting_heightTable.php"]').show();
            $('#stage option[value="scripts/osipvTable.php"]').show();
          } else if(2==num) {
            $('#stage option[value="scripts/functional_statusTable.php"]').show();
            $('#stage option[value="scripts/social_networkTable.php"]').hide();
            $('#stage option[value="scripts/spine_bone_densityTable.php"]').show();
            $('#stage option[value="scripts/general_healthTable.php"]').show();
            $('#stage option[value="scripts/sitting_heightTable.php"]').hide();
            $('#stage option[value="scripts/osipvTable.php"]').hide();
          } else if(1==num) {
            $('#stage option[value="scripts/sitting_heightTable.php"]').hide();
            $('#stage option[value="scripts/general_healthTable.php"]').hide();
            $('#stage option[value="scripts/social_networkTable.php"]').hide();
            $('#stage option[value="scripts/spine_bone_densityTable.php"]').hide();
            $('#stage option[value="scripts/sitting_heightTable.php"]').show();
            $('#stage option[value="scripts/osipvTable.php"]').hide();
          }
          $('#from').val(date_ranges[num].min_date);
          $('#to').val(date_ranges[num].max_date);
          console.log('date range: ' + date_ranges[num].min_date + ' -> ' + date_ranges[num].max_date);
        });
      });
    </script>
  </head>
  <body>
    <div>
      <h1 class="title">CLSA-&Eacute;LCV QAC</h1>
    </div>
    <div class="view">
      <span class="help">
        <ul>
          <li>Please select the wave of interest (eg., 1 = baseline)</li>
          <li>Enter a start date and an end date for the report</li>
          <li>Select an interview stage</li>
        </ul>
      </span>
      <form id="qacform" action="scripts/grip_strengthTable.php" method="POST">
        <div>
          <label for="rank">Wave:</label>
          <input type="number" id="rank" name="rank"
            <?php echo "min={$min_rank} max={$max_rank} step=\"1\" value={$min_rank}"; ?>>
        </div>
        <div>
          <label for="from">Start Date:</label>
          <input type="text" id="from" name="from">
        </div>
        <div>
          <label for="to">End Date:</label>
          <input type="text" id="to" name="to">
        </div>
        <div>
          <label for="stage">Stage:</label>
          <select name="stage" id="stage">
            <option value="scripts/bloodTable.php">Blood Qnaire</option>
            <option value="scripts/blood_pressureTable.php">Blood Pressure</option>
            <option value="scripts/chair_riseTable.php">Chair Rise</option>
            <option value="scripts/cognitive_testTable.php">Cognitive Test</option>
            <option value="scripts/contraindicationsTable.php">ContraIndications Qnaire</option>
            <option value="scripts/carotid_intima_stillTable.php">Carotid Intima Still</option>
            <option value="scripts/carotid_intima_cineloopTable.php">Carotid Intima Cineloop</option>
            <option value="scripts/carotid_intima_srTable.php">Carotid Intima SR</option>
            <option value="scripts/disease_symptomsTable.php">Disease Symptoms Qnaire</option>
            <option value="scripts/dual_hip_bone_densityTable.php">DEXA Dual Hip</option>
            <option value="scripts/forearm_bone_densityTable.php">DEXA Forearm</option>
            <option value="scripts/lateral_bone_densityTable.php">DEXA Lateral</option>
            <option value="scripts/spine_bone_densityTable.php">DEXA Spine</option>
            <option value="scripts/whole_body_bone_densityTable.php">DEXA Whole Body</option>
            <option value="scripts/ecgTable.php">ECG</option>
            <option value="scripts/event_pmtTable.php">Event PMT Qnaire</option>
            <option value="scripts/four_metre_walkTable.php">4 m Walk</option>
            <option value="scripts/functional_statusTable.php">Functional Status Qnaire</option>
            <option value="scripts/general_healthTable.php">General Health Qnaire</option>
            <option value="scripts/grip_strengthTable.php">Grip Strength</option>
            <option value="scripts/hearingTable.php">Hearing</option>
            <option value="scripts/hips_waistTable.php">Hips Waist</option>
            <option value="scripts/osipvTable.php">OSIPV Qnaire</option>
            <option value="scripts/retinal_scanTable.php">Retinal Scan</option>
            <option value="scripts/sitting_heightTable.php">Sitting Height</option>
            <option value="scripts/spirometryTable.php">Spirometry</option>
            <option value="scripts/standing_balanceTable.php">Standing Balance</option>
            <option value="scripts/standing_heightTable.php">Standing Height</option>
            <option value="scripts/time_based_pmtTable.php">Time Based PMT Qnaire</option>
            <option value="scripts/tonometerTable.php">Tonometer</option>
            <option value="scripts/tugTable.php">TUG</option>
            <option value="scripts/urineTable.php">Urine Qnaire</option>
            <option value="scripts/visionAcuityTable.php">Vision Acuity</option>
            <option value="scripts/weightTable.php">Weight</option>
          </select>
        </div>
        <div class="stat-option">
          <label for="stat-option">Statistic:</label>
          <select name="stat-option" id="stat-option">
            <option value="mode">Mode Calculation</option>
            <option value="mean">Mean Calculation</option>
          </select>
        </div>
        <div class="button">
          <button type="submit">Request Data</button>
        </div>
      <form>
    </div>
  </body>
</html>
