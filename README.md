Clone the repository, edit auth.php in the /app folder. Here you can set a username and password, so a user has to log in to use the tool. You can find it below line 14, specifically below "$collection->updateOne". Save and exit the file and build or run the container from the root folder where the dockerfiles are located. 

You can modify docker-compose and Dockerfile, you want the containers to run on different ports.
webserver runs on 6148:80 
MongoDB runs on 27017:27017

Run "docker-compose up -d" to start the containers, you can also build the image if you would desire to do so.
Building MongoDB takes roughly 170 seconds at times, so bare with it.


