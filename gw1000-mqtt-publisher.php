    <?php 
    
    require('/home/pi/work/phpMQTT/phpMQTT.php');

    $server = 'localhost';     // change if necessary
    $mqport = 1883;                     // change if necessary
    $username = 'ruedi';                   // set your username
    $password = '4312';                   // set your password
    $client_id = 'gw1000-mqtt-publisher';
    
    $host = '192.168.110.233';  // Host address 
    $port = 9500;        // Port number 
    
    # Conversion factors
    $f_mph_kmh = 1.60934;
    $f_mph_kts = 0.868976;
    $f_mph_ms = 0.44704;
    $f_in_hpa = 33.86;
    $f_in_mm = 25.4;

     
    // Create a TCP/IP socket 
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
    if ($socket === false) { 
        die("Could not create socket: " . socket_strerror(socket_last_error())); 
    } 
     
    // Bind the socket to the address and port 
    socket_bind($socket, $host, $port) or die("Could not bind to socket"); 
     
    // Start listening for connections 
    socket_listen($socket); 
     
    echo "Listening on $host:$port...\n"; 
     
    while (true) { 
        // Accept a connection 
        $client = socket_accept($socket); 
        if ($client === false) { 
            echo "Could not accept connection: " . socket_strerror(socket_last_error($socket)); 
            continue; 
        } 
     
        // Read data from the client 
        $data = socket_read($client, 1024); 


        $mystr = substr($data,strpos($data,"PASSKEY")); //substr($ourstr,);
    
        $mystr1 = '{"' . $mystr . '"}';
     
        $temp = str_replace('&',',',$mystr1);
        $temp1 = str_replace(',','","',$temp);
        $temp = str_replace('=','":"', $temp1);
        $phpObject = json_decode($temp); 
     
        $temp = get_object_vars($phpObject);

        //taken from index.php https://github.com/iz0qwm/ecowitt_http_gateway
     
        @$temp['PASSKEY'] = '"'. $temp['PASSKEY'] . '"';    
        @$temp['tempinc'] = round( ( $temp['tempinf'] - 32 ) * 5 / 9, 2 );     
        @$temp['tempc']  = round( ( $temp['tempf'] - 32 ) * 5 / 9, 2 );
    
        @$temp['windgustkmh'] = round( $temp['windgustmph'] * $f_mph_kmh, 2 );
        @$temp['windspeedkmh'] = round( $temp['windspeedmph'] * $f_mph_kmh, 2 );
        @$temp['windspeedms'] = round( $temp['windspeedmph'] * $f_mph_ms, 2 );
        @$temp['windgustkmh'] = round( $temp['windgustmph'] * $f_mph_kmh, 2 );
        @$temp['maxdailygustkmh'] = round( $temp['maxdailygust'] * $f_mph_kmh, 2 );


        @$temp['windchillc'] = round((13.12 + 0.6215 * @$temp['tempc'] - 11.37 * pow(@$temp['windspeedkmh'],0.16) + 0.3965 * @$temp['tempc'] * pow(@$temp['windspeedkmh'],0.16)), 1);
        @$temp['windchillf'] = round( ( $temp['windchillc'] * 9 / 5 ) + 32, 2 );
        @$temp['dewptc'] = round(((pow(($temp['humidity']/100), 0.125))*(112+0.9*@$temp['tempc'])+(0.1*@$temp['tempc'])-112),1);
        @$temp['dewptf'] = round( ( $temp['dewptc'] * 9 / 5 ) + 32, 2 );

        @$temp['rainmm'] = round( $temp['erain_piezo'] * $f_in_mm, 2 );
        @$temp['dailyrainmm'] = round( $temp['drain_piezo'] * $f_in_mm, 2 );
        @$temp['weeklyrainmm'] = round( $temp['wrain_piezo'] * $f_in_mm, 2 );
        @$temp['monthlyrainmm'] = round( $temp['mrain_piezo'] * $f_in_mm, 2 );
        @$temp['yearlyrainmm'] = round( $temp['yrain_piezo'] * $f_in_mm, 2 );
        @$temp['rainratemm'] = round( $temp['rrain_piezo'] * $f_in_mm, 2 );

        # Baros
        @$temp['baromabshpa'] = round( $temp['baromabsin'] * $f_in_hpa, 2 );
        @$temp['baromrelhpa'] = round( $temp['baromrelin'] * $f_in_hpa, 2 );

        // print_r($temp);
      
        // https://github.com/bluerhinos/phpMQTT

        $mqtt = new Bluerhinos\phpMQTT($server, $mqport, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {    

            foreach($temp as $key => $value) {
    //            echo $value . "\n";
                if  (is_numeric($value)) {
                    $float = (float) $value;
                    $mqtt->publish('wetter/gw1000/'.$key,$float ,0,false);     
                } else {
                    $mqtt->publish('wetter/gw1000/'.$key,$value ,0,false);
                }
            }
        }
     
        $mqtt->close();

        // Close the client connection 
        socket_close($client); 
        
    } 
     
    // Close the main socket 
    socket_close($socket); 
            

    ?> 


