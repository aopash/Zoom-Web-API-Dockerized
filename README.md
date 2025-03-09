# **Zoom Web API - Dockerized**

This repository provides a Dockerized version of a Zoom API web interface for managing users, meetings, and executing various commands via a web-based interface. The application runs on Apache with PHP.

---

## **Prerequisites**
- **Git**: [Install Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- **Docker**: [Install Docker](https://docs.docker.com/engine/install/ubuntu/#installation-methods)
- **Docker Compose** (if using `docker-compose.yml`): [Install Docker Compose](https://docs.docker.com/compose/install/)

---


## **Installation and Deployment**

### **Option 1: Pull and Run from DockerHub**
To run the container directly from DockerHub:

1. **Pull the image:**
   ```sh
   docker pull aopash/zoom-web-api:latest
   ```

2. **Run the container:**
   ```sh
   docker run -d -p 6148:80 --name zoom-web-api aopash/zoom-web-api:latest
   ```

3. **Access the application:**
   Open a browser and go to:
   ```
   http://localhost:6148
   ```

---

### **Option 2: Clone and Build Locally**
To build and run the container manually:

1. **Clone the repository:**
   ```sh
   git clone https://github.com/aopash/Zoom-Web-API-Dockerized.git
   cd Zoom-Web-API-Dockerized
   ```

2. **Build the Docker image:**
   ```sh
   docker build -t aopash/zoom-web-api:latest .
   ```

3. **Run the container:**
   ```sh
   docker run -d -p 6148:80 --name zoom-web-api aopash/zoom-web-api:latest
   ```

4. **Verify the logs (if needed):**
   ```sh
   docker logs zoom-web-api
   ```

---

### **Option 3: Using `docker-compose`**
For running with MongoDB and logging enabled:

1. **Clone the repository:**
   ```sh
   git clone https://github.com/aopash/Zoom-Web-API-Dockerized.git
   cd Zoom-Web-API-Dockerized
   ```

2. **Start the services:**
   ```sh
   docker-compose up -d
   ```

3. **Stop the services:**
   ```sh
   docker-compose down
   ```

## **License**
This project is licensed under the MIT License. See the `LICENSE` file for details.

