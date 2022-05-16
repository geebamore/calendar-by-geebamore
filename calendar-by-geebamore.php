<?php
/*
Plugin Name: Calender By Geebamore
Description: A plugin to display calender in wordpress
Author: Geebamore
Version: 0.1
Author Uri: https://geebamore.github.io/portfolio
*/
if(defined( 'ABSPATH' )){
add_action('admin_menu', 'setup_menu');
 add_shortcode('my_calendar','init');
function setup_menu(){
    add_menu_page( 'Main', 'Calender Plugin', 'manage_options', 'Calender Plugin', 'init' );

} 
 

 function init(){
     global $wpdb;
    $wpdb->query(
        $wpdb->prepare("CREATE TABLE `wp_calender` ( `id` INT(100) NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NOT NULL , `location` VARCHAR(100) NOT NULL , `startDate` DATE NOT NULL , `endDate` DATE NOT NULL , PRIMARY KEY (`id`))"
    ));
   
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://unpkg.com/js-year-calendar@latest/dist/js-year-calendar.min.css" />
<script src="https://unpkg.com/js-year-calendar@latest/dist/js-year-calendar.min.js"></script>
 <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <style type="text/css">.container{width: 100% !important;}</style>
 <div id="calendar"></div>
<div class="container" >
    <?php 
if(is_user_logged_in() && is_super_admin()){
showform();
}?>
</div>

<script>
var calendar = null;

jQuery(function() {

    calendar = new Calendar('#calendar', {       
        mouseOnDay: function(e) {
            if(e.events.length > 0) {
                var content = '';
                
                for(var i in e.events) {
                    content += '<div class="event-tooltip-content">'
                                    + '<div class="event-name" style="color:' + e.events[i].color + '">' + e.events[i].name + '</div>'
                                    + '<div class="event-location">' + e.events[i].location + '</div>'
                                + '</div>';
                }
            
                jQuery(e.element).popover({ 
                    trigger: 'manual',
                    container: 'body',
                    html:true,
                    content: content
                });
                
                jQuery(e.element).popover('show');
            }
        },
        mouseOutDay: function(e) {
            if(e.events.length > 0) {
                setTimeout(function(){
                    jQuery(e.element).popover('hide');
                },3000);
            }
        },
        dayContextMenu: function(e) {
            jQuery(e.element).popover('hide');
        },
        
        dataSource: [
        //php display data
        <?php 
             $result1=$wpdb->get_results("Select * from wp_calender");
       foreach ( $result1 as $print )   {
        $date=$print->startDate; 
        ?>
        {
        id: <?php echo $print->id ; ?>,
        name: '<?php echo $print->name; ?>',
        location: '<?php echo $print->location; ?>',
       startDate: new Date("<?php echo date('Y',strtotime($print->startDate));?>,<?php echo date('m',strtotime($print->startDate)); ?>,<?php echo date('d',strtotime($print->startDate)); ?>"),
       endDate: new Date("<?php echo date('Y',strtotime($print->endDate));?>,<?php echo date('m',strtotime($print->endDate)); ?>,<?php echo date('d',strtotime($print->endDate)); ?>")
        },
<?php
}
 ?>
 //php display data end

]    
}); //calendar function ends 
}); //jquery end

</script>
<!-- End of script display -->

<?php       
}
//End Of ABSPATH
if(isset($_POST['startDate']) && isset($_POST['endDate']) && isset($_POST['name']) && isset($_POST['enter'])){
    $enD=$_POST['endDate'];
    $stD=$_POST['startDate'];
    $nme=$_POST['name'];
    $loc=$_POST['location'];
    $wpdb->insert("wp_calender",array(
        'startDate' => $stD ,
        'endDate' => $enD ,
        'name' => $nme ,
        'location' => $loc
    ));
}
if(isset($_POST['delete'])){
    $wpdb->query('Truncate wp_calender');
}

}
else{
    echo "<script>window.location.href='/'</script>";
}
//end of plugin
//Display form 
function showform(){
 ?>

<hr><hr>
<style type="text/css">
    @media screen and (max-width: 1140px){
        .custom_form{display: flex; flex-flow: column;}
    }
</style>
<div class=" custom_form"  style="width: 100%;display: flex; justify-content:space-between; align-content:center; width: 100%;">
    <div class="col-lg-4" >
        <h2>Settings</h2>
        <form method="post" action= "" style="display:flex; align-content:center; justify-content:space-between; flex-flow:column;">
            <label>Name &nbsp;</label><input type="text" name="name" placeholder="Enter a task name...."><br><br>
            <label>Location &nbsp;</label><input type="text" name="location" placeholder="Enter a location...."><br><br>
            <label>Start Date &nbsp;</label><input  type="date" name="startDate" placeholder="Enter  start date...."><br><br>
            <label>End Date &nbsp;</label><input type="date" name="endDate" placeholder="Enter end date ...."><br><br>
            <div class="container">
            <input type="submit" style="width:100px; height: 50px;" name="enter">&nbsp;
            <input type="submit" style="width:100px; height: 50px; background-color: red; border:none;color:white" value="Delete ALL" name="delete">
        </div>
        </form><br><br>
    </div>
    <div class="col-lg-4" >
        <form method="post" action="" enctype="multipart/form-data">
        <div class="container">
        <input type="file" name="file">
        <input type="submit" name="imp_buttn" style="width:100px; height: 50px;" value="Import csv">
    </div><br><br>
    <div class="container">
    <input type="submit" value="Export" name="export" style="width:100px; height: 50px;">
    </div><br><br>
    </form><br><br>
    </div>
</div>
 <?php
}
//Display form ends

 function on_deactivation()
    {
       global $wpdb;
       $sql="Drop table wp_calender";
       $wpdb->query($sql);
    }
   register_deactivation_hook(__FILE__, 'on_deactivation');



function export_data(){
    if(! file_exists('../wp-content/plugins/calendar-by-geebamore/cal_uploads')){
        mkdir('../wp-content/plugins/calendar-by-geebamore/cal_uploads');
    }
     global $wpdb;
     $query12= $wpdb->get_results("Select * from wp_calender");
     if(!empty($query12)){
$file = fopen("../wp-content/plugins/calendar-by-geebamore/cal_uploads/".strtotime (date('Y-m-d h:i:s')).".csv","w");

foreach ($query12 as $line) {
    $data=array($line->id,$line->name,$line->location,date($line->startDate),date($line->endDate));
  fputcsv($file, $data);
}

fclose($file);
}
else{
    echo "<script>alert('Please note no data found !!');</script>";
}
} 

if(isset($_POST['export'])){
    export_data();
}


function import_data(){
    global $wpdb;

    
    $fileName = $_FILES["file"]["tmp_name"];
    
    if ($_FILES["file"]["size"] > 0) {
        
        $file = fopen($fileName, "r");
        
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            $userName = "";
            if (isset($column[1])) {
                $userName = ($column[1]);
                echo $userName;
            }
            $password = "";
            if (isset($column[2])) {
                $password = ($column[2]);
                echo $password;
            }
            $firstName = "";
            if (isset($column[3])) {
                $firstName = ($column[3]);
                echo $firstName;
            }
            $lastName = "";
            if (isset($column[4])) {
                $lastName = ($column[4]);
                echo $lastName;
            }
            
            $paramArray = array(
                'name' =>$userName,
                'location' =>$password,
                'startDate' =>$firstName,
                'endDate'=>$lastName
            );
            $insertId=$wpdb->insert('wp_calender',$paramArray);
            if (! empty($insertId)) {
                $type = "success";
                $message = "CSV Data Imported into the Database";
                echo "<script>console.log('Complete');</script>";
            } else {
                $type = "error";
                $message = "Problem in Importing CSV Data";
            }
        }
    }
}

//end of import

if (isset($_POST["imp_buttn"])) {
import_data();
}
?>