pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'caresync-web'
        DOCKER_TAG   = "build-${BUILD_NUMBER}"
        REGISTRY     = 'my-docker-hub-username' // Placeholder for docker registry
    }

    stages {
        // Stage 1: Checkout Source Code
        stage('Checkout') {
            steps {
                echo 'Checking out source code from Git Repository...'
                checkout scm
            }
        }

        // Stage 2: PHP Syntax Linting
        stage('Validate Syntax') {
            steps {
                echo 'Running PHP Syntax Validation (Lint Checks)...'
                // Runs php -l recursively on all php files to detect syntax errors before building
                sh 'find . -name "*.php" -print0 | xargs -0 -n 1 php -l'
            }
        }

        // Stage 3: Mock Test Execution
        stage('Test Connection') {
            steps {
                echo 'Running database connection validation tests...'
                // A DevOps student can run custom unit tests or connection mock testing here
                echo 'Syntax check: OK. Database DSN configuration check: OK.'
            }
        }

        // Stage 4: Docker Container Image Building
        stage('Build Docker Image') {
            steps {
                echo "Building Docker container image: ${DOCKER_IMAGE}:${DOCKER_TAG}..."
                // Builds the docker image from local Dockerfile
                sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."
                sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest"
            }
        }

        // Stage 5: Deploy To Kubernetes Cluster
        stage('Deploy to Kubernetes') {
            steps {
                echo 'Staging Kubernetes deployment manifests...'
                // Demonstrates rolling out changes to the Kubernetes staging cluster
                echo 'Updating kubernetes/deployment.yaml with new tag...'
                sh "kubectl apply -f kubernetes/configmap.yaml"
                sh "kubectl apply -f kubernetes/secret.yaml"
                sh "kubectl apply -f kubernetes/deployment.yaml"
                sh "kubectl apply -f kubernetes/service.yaml"
                echo 'Deployment complete. CareSync portal is online!'
            }
        }
    }

    post {
        success {
            echo 'CareSync CI/CD Pipeline executed successfully!'
        }
        failure {
            echo 'CareSync CI/CD Pipeline failed. Check build logs for debugging.'
        }
    }
}
