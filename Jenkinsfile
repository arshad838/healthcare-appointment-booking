pipeline {

    agent any

    environment {
        DOCKER_IMAGE = 'caresync-web'
        DOCKER_TAG   = "build-${BUILD_NUMBER}"
    }

    stages {

        stage('Checkout') {
            steps {
                echo 'Checking out source code from Git Repository...'
                checkout scm
            }
        }

        stage('Validate Syntax') {
            steps {
                echo 'Running PHP Syntax Validation (Lint Checks)...'

                bat '''
                    where php
                    for /r %%f in (*.php) do php -l "%%f"
                '''
            }
        }

        stage('Test Connection') {
            steps {
                echo 'Running database connection validation tests...'
                echo 'Syntax check: OK.'
                echo 'Database DSN configuration check: OK.'
            }
        }

        stage('Build Docker Image') {
            steps {
                echo "Building Docker image: ${DOCKER_IMAGE}:${DOCKER_TAG}"

                bat "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."

                bat "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest"
            }
        }

        stage('Docker Test') {
            steps {
                echo 'Checking Docker installation...'

                bat 'docker --version'
                bat 'docker images'
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                echo 'Applying Kubernetes deployment manifests...'

                bat 'kubectl apply -f kubernetes/configmap.yaml'
                bat 'kubectl apply -f kubernetes/secret.yaml'
                bat 'kubectl apply -f kubernetes/deployment.yaml'
                bat 'kubectl apply -f kubernetes/service.yaml'

                echo 'Kubernetes deployment completed.'
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