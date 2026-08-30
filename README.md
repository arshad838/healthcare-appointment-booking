# CareSync: Healthcare Appointment Booking System

CareSync is a complete, responsive, and secure **Healthcare Appointment Booking System** developed in native PHP 8.3+ and MySQL. It is designed as a university DevOps practical assignment, demonstrating containerization (Docker), local multi-service composition (Docker Compose), CI/CD automation pipelines (Jenkinsfile), and container orchestration (Kubernetes manifests).

---

## 1. Project Overview
CareSync addresses patient-specialist scheduling challenges by replacing traditional phone coordination with an interactive, live web portal. The system coordinates schedules across three user roles: **Administrators** (management & reports), **Doctors** (accept/reject/complete visits), and **Patients** (browse doctors & book slots).

---

## 2. Features
* **Authentication Security**: Password hashing via bcrypt (`password_hash()`) and session-hijack protections.
* **Interactive Booking Scheduler**: An asynchronous (AJAX) calendar loader that renders real-time time slots for a doctor, overlaying previous bookings to prevent double-booking.
* **Role-Based Dashboards**: Customizable portals for Admin, Doctor, and Patient access.
* **Analytics Reports**: Compiled clinic statistics, specialty revenues, and status counts with a print-friendly CSS template.
* **DevOps Ready**: Out-of-the-box support for Docker containers, local compose stacks, Jenkins validation pipelines, and Kubernetes deployment configurations.

---

## 3. Technology Stack
* **Language**: PHP 8.3+
* **Database**: MySQL 8.0+ / MariaDB
* **Frontend**: HTML5, CSS3, Bootstrap 5, Font Awesome 6, JavaScript (ES6+), jQuery
* **Database Driver**: PHP Data Objects (PDO) for injection prevention

---

## 4. System Requirements
* PHP 8.3 or newer
* MySQL 8.0 or newer
* Apache or Nginx Web Server
* Docker Engine / Docker Desktop (optional, for containerization)
* Kubernetes Cluster (Minikube / Kind) & `kubectl` CLI (optional)
* Jenkins Server (optional, for CI/CD stages)

---

## 5. Environment Variables
The application queries system variables for database credentials, falling back to local credentials when not set.
| Variable Name | Default Value | Description |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | Database Server Host address |
| `DB_NAME` | `healthcare_booking` | Database Name |
| `DB_USER` | `root` | Database Login Username |
| `DB_PASSWORD` | `""` (empty string) | Database Login Password |

---

## 6. Demo Credentials
For testing and classroom grading, the following accounts are pre-seeded in the database:
> [!IMPORTANT]
> The default password for **ALL** pre-seeded demo accounts is: `password123`

* **Administrator**:
  * Email: `admin@healthcare.com`
* **Doctor (Cardiology)**:
  * Email: `john.smith@healthcare.com`
* **Patient**:
  * Email: `alice.brown@example.com`

---

## 7. Project Structure
```text
healthcare-booking/
├── admin/                     # Administrator Pages
│   ├── dashboard.php          # KPI summaries and ChartJS chart
│   ├── doctors.php            # Active doctors index & status toggle
│   ├── doctor-add.php         # Create new doctor user and profile
│   ├── doctor-edit.php        # Edit doctor credentials & specialization
│   ├── departments.php        # Clinical departments CRUD
│   ├── patients.php           # Patient registry viewer
│   ├── appointments.php       # System-wide booking manager
│   ├── schedules.php          # Configure doctor weekly availability shifts
│   └── reports.php            # Revenue and booking stats reports
│
├── doctor/                    # Doctor Portal Pages
│   ├── dashboard.php          # Today's lists & upcoming stats
│   ├── appointments.php       # Manage bookings (Accept/Reject/Complete)
│   ├── patients.php           # List of patients consulted
│   └── profile.php            # Edit doctor details (fee, bio, credentials)
│
├── patient/                   # Patient Portal Pages
│   ├── dashboard.php          # Scheduled bookings & support panels
│   ├── doctors.php            # Browse active doctor catalog
│   ├── doctor-details.php     # Doctor details & interactive booking form
│   ├── book-appointment.php   # AJAX time-slot checker and POST processor
│   ├── appointments.php       # View booking history and cancel slots
│   └── profile.php            # Update patient profile fields
│
├── auth/                      # Authentication Flow
│   ├── login.php              # Secure sign in card
│   ├── register.php           # Patient registration form
│   ├── process-login.php      # Verify password_verify() and session init
│   └── logout.php             # Session destruction page
│
├── config/
│   └── database.php           # PDO connector resolving environment variables
│
├── includes/                  # Common Layout Components
│   ├── header.php             # HTML head imports (Bootstrap, fonts, styles)
│   ├── footer.php             # Page endings, scripts, public footer
│   ├── navbar.php             # Responsive top navbar with user profile menu
│   ├── sidebar.php            # Responsive dashboard sidebar navigation
│   ├── auth.php               # Role checking helper functions
│   └── functions.php          # Date/time formatters, time slot generators
│
├── assets/                    # Static Assets
│   ├── css/
│   │   └── style.css          # Core CSS stylesheet overrides
│   └── js/
│       └── script.js          # Forms validation and AJAX calendar loader
│
├── database/
│   └── healthcare_booking.sql # SQL schema and seeded clinical data
│
├── kubernetes/                # Kubernetes Manifests
│   ├── configmap.yaml         # App configuration values
│   ├── secret.yaml            # Base64 database passwords
│   ├── deployment.yaml        # Web and DB container pod specs
│   └── service.yaml           # Service configuration (NodePort / ClusterIP)
│
├── index.php                  # Public clinic landing page
├── Dockerfile                 # PHP 8.3 + Apache web server container spec
├── docker-compose.yml         # Dev/Prod multi-container runner
├── Jenkinsfile                # Automated CI/CD Declarative pipeline script
└── .gitignore                 # Files excluded from git tracking
```

---

## 8. Installation

### Local XAMPP Setup (Windows)
1. Open XAMPP and start **Apache** and **MySQL**.
2. Clone the repository into `C:\xampp\htdocs\healthcare-booking` (or `d:\xampp\htdocs\healthcare-booking`).
3. Open phpMyAdmin (`http://localhost/phpmyadmin/`), create a database named `healthcare_booking`.
4. Import the SQL script found in `database/healthcare_booking.sql` to initialize the database tables and seed accounts.
5. Open your browser and navigate to `http://localhost/healthcare-booking/`.

### Docker & Docker Compose Setup
To run the entire system inside isolated containers without installing Apache or PHP locally:
1. Ensure Docker Desktop is installed and running.
2. Navigate to the project root and start the container stack:
   ```bash
   docker compose up -d
   ```
3. Docker Compose will pull the MySQL 8 image, compile the PHP 8.3 Apache web container, link them together in a secure network, and load the database seed file automatically.
4. Access the CareSync application at `http://localhost:8080/`.

* **Stop Containers**:
  ```bash
  docker compose down
  ```
* **View Container Status**:
  ```bash
  docker compose ps
  ```
* **Inspect Container Logs**:
  ```bash
  docker compose logs -f
  ```

---

## 9. Kubernetes Deployment
For Minikube or local Kubernetes clusters:
1. Start your local cluster:
   ```bash
   minikube start
   ```
2. Navigate to the root directory and apply all manifests:
   ```bash
   kubectl apply -f kubernetes/configmap.yaml
   │   kubectl apply -f kubernetes/secret.yaml
   │   kubectl apply -f kubernetes/deployment.yaml
   │   kubectl apply -f kubernetes/service.yaml
   ```
3. Verify that the pods are running successfully:
   ```bash
   kubectl get pods
   ```
4. Expose the web portal to your browser:
   ```bash
   minikube service caresync-web-service
   ```
   *Note: In production, the service will listen on NodePort `30080` (e.g. `http://<node-ip>:30080`).*

---

## 10. Jenkins Pipeline CI/CD Stage Explanations
The included `Jenkinsfile` provides a declarative pipeline automating:
1. **Checkout**: Pulls the current branch from the SCM code repository.
2. **Validate Syntax**: Runs a recursive script checking all `.php` files for syntax compile errors (`php -l`).
3. **Test Connection**: Runs mock checks verifying routing patterns and connection drivers.
4. **Build Docker Image**: Builds the Docker container using `Dockerfile` and tags it with the unique build number.
5. **Deploy**: Runs `kubectl apply` to roll out configuration changes, secrets, deployment updates, and services to the Kubernetes cluster.

---

## 11. Security Considerations
* **SQL Injection Prevention**: Prepared statements with typed variables via PDO.
* **Cross-Site Scripting (XSS)**: Strict output escaping on all user records before browser rendering.
* **Cross-Site Request Forgery (CSRF)**: Random-generated crypto-hashes validated on form actions.
* **Double-Booking Enforcements**: Validates doctor-date-time availability before saving scheduling updates.

---

## 12. Troubleshooting
* **Database Connection Failures**: Check that the MySQL database is running and verify host configurations (`127.0.0.1` locally vs. `db` service hostname inside Docker/Kubernetes network).
* **Missing PDO Extension**: Ensure `extension=pdo_mysql` is uncommented in your local XAMPP `php.ini`.
* **CSS/Asset Path Issues**: The application detects paths dynamically. If assets fail to load, ensure the index is located inside `/healthcare-booking/` or the server root `/`.
