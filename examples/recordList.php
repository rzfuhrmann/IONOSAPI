<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <?php
            $credentials = include __DIR__.'/../credentials/credentials.main.php';

            require_once __DIR__.'/../class.IONOSAPI.php';
            $ionos = new IONOSAPI($credentials['publicprefix'], $credentials['secret']); 
            
            $zones = $ionos->getZones(); 
        
            echo '<table>';
            foreach ($zones as $zone){
                foreach ($zone["records"] as $record){
                    
                    echo '<tr>';
                    echo '<td>'.$zone["name"].'</td>';
                    echo '<td>'.$record["name"].'</td>';
                    echo '<td>'.$record["type"].'</td>';
                    echo '<td>'.$record["content"].'</td>';
                    echo '<td>'.$record["changeDate"].'</td>';
                    echo '<td>'.$record["ttl"].'</td>';
                    echo '<td>'.($record["disabled"]?'disabled':'enabled').'</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        ?>
    </body>
</html>