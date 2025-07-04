# ecowitt gw1000 Gateway to mqtt by hb9fvk (ruedi) 

You need a 
	-	gw1000 Gateway
	-	raspberry with php and mqtt server

Setup

	mqtt

		$server = 'localhost';     // change if necessary
		$mqport = 1883;                     // change if necessary
		$username = 'xxxx';                   // set your username
		$password = 'xxxx';                   // set your password
		$client_id = 'gw1000-mqtt-publisher';

	server for gw1000

		$host = 'your ip';  // Host address
		$port = 9500;        // Port number

enable weather service on ecowitt app with ip address and port time 60 seconds
	
	
Setup for systemd

copy file "gw1000-mqtt-publisher.service" to  /etc/systemd/system/

and enter the following to enable the service

		sudo systemctl daemon-reload

		sudo systemctl enable gw1000-mqtt-publisher.service

		sudo systemctl start gw1000-mqtt-publisher.service

check the status

		sudo systemctl status gw1000-mqtt-publisher.service

