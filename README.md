
# IoT application with docker containers

## 1. Authors
1. Marc Vivas Baiges

## Table of Contents
1. [Authors](#1-authors)
2. [Introduction](#2-introduction)
3. [UML class diagram](#3-uml-class-diagram)
4. [How to run the application](#4-how-to-run-the-application)
5. [Description of each component](#5-description-of-each-component)
6. [Design decisions](#6-design-decisions) 
7. [Conclusions and time spent](#7-conclusions)
8. [References](#8-references)

## 2. Introduction
This project consists of the creation of an IoT application.  

![alt text](IoT_application.jpg "IoT_application")  
  
> Note 1: Sensors will be simulated using docker containers.  
<br>  

## 3. UML Class Diagram
![UML Class Diagram](api_uml_diagram.png)

## 4. How to run the application

First, you have to go to the folder where `docker-compose.yml` is located.  

Once there, you can execute the next command to run the application.

```bash
sudo docker compose up --scale sensor=1
```

> Note: The number of sensors can be changed by increasing the number of the command. (sensor=2, sensor=3 ...)  

You will have to wait for all the containers to be up and running, this takes a bit of time. Sometimes
while the application is starting you could see something to crash, not always but, don't worry, the service
will restart and stabilize, don't touch anything!. 

To know if the application is running, you should see messages like this in the terminal:
```
project-sensor-4            | Sensor 1 has now sent a message to the server!
project-server-1            | Server: Message received from sensor 1
project-server-1            | The server has now sent data from the sensor 1 to the analytics module!
project-analytics_module-1  | ------------- Analytics module -------------
project-analytics_module-1  | Message received!
project-analytics_module-1  | ConsumerRecord(topic='analytics', partition=0, offset=35, timestamp=1669453778454, timestamp_type=0, key=None, value={'v': '21.0', 'ts': '2022-11-26 09:09:38', 'sensor': '44b4f68f5c2e', 'id': 1}, headers=[], checksum=None, serialized_key_size=-1, serialized_value_size=70, serialized_header_size=-1) is being processed
project-sensor-2            | Sensor 2 has now sent a message to the server!
```

If you want to run just the Rest API and SQLite, you just have to write the next command.
```bash
sudo docker compose up MyClimateAPI
```

> Keep in mind that if you want to interact with the API, you have to send all the requests to
`localhost:8000/`.

## 5. Description of each component
### Sensors
Sensors first register to the app using the REST API. They will act as a user, creating a user
with a house containing one sensor.   

Sensors send the data they collect from `data2.csv` to the server by using an MQTT broker.
Sensors publish the gathered data to the `temperature` topic every 10 seconds. The server will 
subscribe to this topic in order to receive the sensor's information.    

The messages the server will receive are in JSON format and have this structure:
```node
let message = {
  "sensorId": String, // Identifier of the sensor. It's the container HOSTNAME.
  "temperature": Number, // Temperature captured by the sensor, extracted from the csv file.
  'measured_at': Date, // When the temperature was captured
  'token': String  // API authentication token
}
```

The sensor code is available in the `./IoT_devices` folder, to be more specific the name of the file is `sensor.js`.

### Analytics module
Analytic_module receives sensor data from the server and uses an AI model to make predictions.
The predictions are sent back to the server, which will store them.  

Analytics_module will be consuming sensor data that is being published to the 
`analytics` Kafka topic. Once a new message arrives, the analytics module will make a prediction.
The result will be sent back to the server using the `analytics_results` Kafka topic. 

The messages the server will receive are in JSON format and have this structure:
```node
let message = {
    "ds": Date,         // Collection date
    "yhat": Number,     // Temperature prediction
    "yhat_lower": Number,   // Temperature prediction
    "yhat_upper": Number,   // Temperature prediction
    "temperatureId": String,    // Temperature identifier
    'token': String  // API authentication token
}
```
The analytics module code is available in the `./Analytics_module` folder, to be more specific the name of the file is `consumer.py`.

### Server

The server's responsibility is to receive data from the sensors and the analytics module. Immediately 
after it receives a message, the server will store the data in the database using the Rest API.

#### Sensors ==> Server ===> Analytics_module communication
The server will subscribe to the `temperature` MQTT topic and will wait for new messages.
After receiving a message, the server will store the content of it in the database. As soon as it finishes,
the server will send the sensor data to the Analytics_module using the `analytics` Kafka topic.

The message Analytics_module will get has the following structure:  
```node
let message = {
  "v": Number,    // Captured temperature
  "ts": Date,   // When the data was captured
  "temperatureId": String,    // Temperature identifier
  'token': String  // API authentication token
}
```

#### Analytics_module ===> Server communication
The server consumes data that is sent to the `analytics_results` Kafka topic. 
When it receives a new message, the server stores it in the database.

The server code is available in the `./Cloud_service` folder, to be more specific the name of the file is `server.js`.

### Rest API
The Rest API is responsible for providing other services with the possibility of interacting with the database resources.
The code is available inside the folder named `MyClimateAPI`. 
If you want to read more information about the API, you can read `./API_README.md`.

### Database
The database stores all the information related with sensors, users, houses, measurements and measurements
predictions.

## 6. Design decisions

### Programming languages
The sensors and the cloud service are written in JavaScript. The project could have been written in
any popular language, I've chosen JS because I wanted to gain more experience with it.  
  
The analytics module, which has been given, is written in Python.

Lastly, the Rest API is written in a popular php framework called Laravel because I already 
had experience with it.

### Database
The database that is used is **SQLite**.  
- It was the easiest choice to integrate with Laravel, as it does not require of additional packages.
- It is not the most efficient option. This would be InfluxDB or MongoDB, as they offer an optimized way to store timeseries. 

### Analytics module modification
`consumer.py` has been slightly modified in order to receive and send JSON messages 
instead of Python dictionaries.

Analytics_module now also creates the `analytics_results` Kafka topic if it does not exist. 

Some prints have also been added to keep better track of what the service is doing.


### MQTT
The server and the sensors will be using an npm package called 
[mqtt.js](https://www.npmjs.com/package/mqtt) in order to communicate with the chosen 
MQTT broker, which is called [mosquitto](https://mosquitto.org/).

The reason to use mosquitto is because it was taught during class.

### Kafka
The server and the sensors will be using npm package called [kafka-node](https://www.npmjs.com/package/kafka-node)
to communicate with the chosen Kafka broker, which is called [cp-kafka](https://hub.docker.com/r/confluentinc/cp-kafka/).

I first tried Bitnami Kafka but it didn't work, so I decided to use the alternative I just mentioned.

Kafka needs [Zookeeper](https://zookeeper.apache.org/) to work properly, so this is also installed in
its own container.


### Dockerize
[Dockerize]( https://github.com/jwilder/dockerize) is a tool that allows services to wait for other services to be ready. The next services 
are using dockerize:  
1. Server: Waits for Kafka, Zookeeper, Mosquitto and Rest API to be ready.
2. Sensors: Wait for Mosquitto and Rest API to be ready.
3. Analytic_module: Waits for Kafka and Zookeeper to be prepared.

## 7. Conclusions
The requirements of the statement have been fulfilled. The next goal would be to change the database is being used for a more efficent one. A UI for the owners of the sensors should be also done.
 
Personally, I enjoyed this project, as I learned how to work with Docker, which I find very interesting and useful.  

Approximate time spent: 
 - Project 1: (Sensors, cloud service, analytics module, learn docker, kafka, mqtt) **51 hours**.
 - Project 2: (API and integration with project 1):  **41 hours**.

## 8. References
1. Environment variables: https://stackoverflow.com/questions/52650775/how-to-pass-environment-variables-from-docker-compose-into-the-nodejs-project
2. MQTT.js: https://www.npmjs.com/package/mqtt#end
3. MQTT.js tutorial: https://www.emqx.com/en/blog/mqtt-js-tutorial
4. Create the same service multiple times: https://karthi-net.medium.com/how-to-scale-services-using-docker-compose-31d7b83a6648
5. Callback hell: https://codearmy.co/que-es-el-callback-hell-y-como-evitarlo-4af418a6ed14
6. Kafka tutorial: https://www.jymslab.com/ejemplo-simple-de-kafka-node-js/
7. Dataframe column to datetime: https://stackoverflow.com/questions/26763344/convert-pandas-column-to-datetime
8. Dataframe to json: https://pandas.pydata.org/pandas-docs/stable/reference/api/pandas.DataFrame.to_json.html
9. JSON parse: https://stackoverflow.com/questions/42494823/json-parse-returns-string-instead-of-object
10. Kafka confluent: https://docs.confluent.io/platform/current/installation/docker/config-reference.html#required-confluent-enterprise-ak-settings
11. How to wait other services to be ready: https://github.com/jwilder/dockerize 
12. Insert image, markdown: https://stackoverflow.com/questions/41604263/how-do-i-display-local-image-in-markdown
13. Table of contents, markdown: https://stackoverflow.com/questions/11948245/markdown-to-create-pages-and-table-of-contents/33433098#paragraph2
14. API token authentication: https://laravel.com/docs/9.x/sanctum#api-token-authentication .
