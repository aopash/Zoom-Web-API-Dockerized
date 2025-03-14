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


---

> [!CAUTION]
> Using this tool may cause severe damage

<h2 id="zoom-marketplace">Zoom Marketplace</h2><br>
<ul>
<li>Sign in to your organizations vanity url <a href="https://XYZ-se.zoom.us">https://XYZ-se.zoom.us</a> as you normally would.</li>
<li>Find your way to the marketplace or click this <a href="https://marketplace.zoom.us/">Link to the Marketplace</a></li>
</ul>
<h3 id="building-server-to-server-app">Building Server-to-Server app</h3>
<p>Click down the dropdown menu called “Develop” in the upper right corner of the site, and press on the Build a Server-to-Server App. <br> Choose a valid name for your new project and let’s embark on this journey 🙃<br>
<img src="https://raw.githubusercontent.com/aopash/svglogo/main/marketplace-build-server-to-server-app.png" alt="finding the button to build the app"></p>
<h4 id="going-through-the-options">Going through the options</h4>
<!-- ![Going-through-the-options](https://raw.githubusercontent.com/aopash/svglogo/main/Going-through-the-options.png) -->
<img align="left" src="https://raw.githubusercontent.com/aopash/svglogo/main/Going-through-the-options.png">
  <dl>
  <dt>App Credentials</dt>
    <dd>Note down your credentials and do not share them with anyone.<br>Account ID - is our instances identifier<br>Client ID - is the equivalent of a username<br>Client Secret - is the equivalent of a password.</dd>
  <dt>Information</dt>
    <dd>Here you fill out some basic info related to this app, nothing too important<br>Developer Contact Information<br>- Here you mainly want to include an email that will be contacted incase Zoom decides to deprecate this feature/app.</dd>
  <dt>Feature</dt>
    <dd>We don't really care about this option here. Not used, at least not yet.</dd>
  <dt>Scopes</dt>
    <dd>Permission scope to allow getting user info and also deleting are as following<br><pre>user:write:admin,user:write,user:read:admin,user:read,user_info:read</pre></dd>
</dl>
<br><br><blockquote>
<p>[!NOTE]<br>
Note, permissions might change as more features are added.</p>
</blockquote>
<br clear="left">
<h3 id="activating-app">Activating App</h3>
<p>Simply smash that activation button and WHAM! <br>You have now successfully deployed a Server-to-Server OAUTH app</p>
<p><img src="https://raw.githubusercontent.com/aopash/svglogo/main/activate-app.png" alt="Activating the app"></p>
