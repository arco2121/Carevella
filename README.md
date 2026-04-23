# IOT Project laravel
### Web App in PHP for intefacing with the service

## Install

- Clone project
- Run composer update and install and run npm install, or just 'npm run build'
- Copy the .envXample as .env and copy the following extra config
- Use this as the MQTT config in the .env :
  
    ```env
        MQTT_HOST=0b9a48980b684944aabfe4adc2ebc36b.s1.eu.hivemq.cloud
        MQTT_PORT=8883
        MQTT_CLIENT_ID=caravella
        MQTT_AUTH_USERNAME=IOT-Project
        MQTT_AUTH_PASSWORD=Ccmt1234
    ```

## Run
- Run 'npm run build' once
- Run 'npm run app' or 'npm start'
