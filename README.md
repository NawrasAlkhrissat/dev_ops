# Ticketshop – DevOps & Containerisierung Projekt

Dieses Projekt ist ein minimaler, aber funktionstüchtiger Ticketshop, der als Projektabgabe für das WPK-Modul "DevOps zu Microservices" entwickelt wurde.

---

## 1. Projektdokumentation

### 1.1 Beschreibung der umgesetzten Anwendung
Der Webshop ist eine containerisierte Ticketverwaltung. Er ermöglicht es Benutzern, verfügbare Konzerttickets für verschiedene Events einzusehen. Die Anwendung wurde fachlich einfach gehalten, um den Fokus auf eine technisch saubere Bereitstellung, Orchestrierung und Automatisierung zu legen.

### 1.2 Definition des Funktionsumfangs (Minimalziel)
Der umgesetzte Funktionsumfang umfasst:
* Anzeige einer übersichtlichen Liste von Konzert-Tickets (inkl. Eventname und Preis).
* Dynamische Datenabfrage und -anzeige aus einer relationalen Datenbank.
* Persistente Speicherung der Ticket-Daten, sodass Käufe und Verfügbarkeiten auch nach einem Container- oder Pod-Neustart erhalten bleiben.

### 1.3 Wahl der Programmiersprache & Laufzeitumgebung
Für die Umsetzung des Ticketshops wurde **PHP** in Kombination mit einer **MySQL-Datenbank** gewählt. 
**Grund:** PHP ermöglicht eine einfache und ressourcenschonende Ausführung von Skripten. Als Laufzeitumgebung wird das offizielle `php:8.2-apache` Docker-Image genutzt. Dies ermöglicht eine schnelle Bereitstellung mit integriertem Webserver und nativer Unterstützung für die PDO-MySQL-Erweiterung, ohne zusätzliche Abhängigkeiten auf dem Host-System installieren zu müssen.

### 1.4 Grobe Architekturübersicht (Schichtenmodell)
Die Architektur orientiert sich am Schichtenmodell moderner IT-Infrastrukturen:

| Schicht | Tool | Funktion im Projekt |
| :--- | :--- | :--- |
| **Prozessautomatisierung** | Ansible | Fungiert als Control Node (Infrastructure-as-Code). Übernimmt den automatisierten Build-Prozess und wendet die K8s-Manifeste an. |
| **Orchestrierung** | Kubernetes | Verwaltet die Workloads im Cluster (Minikube). Nutzt Deployments für Ausfallsicherheit und Services (LoadBalancer) für das externe Netzwerk-Routing. |
| **App-Isolation** | Docker | Kapselt die PHP-Webanwendung und ihre Abhängigkeiten in einem reproduzierbaren Image (`Dockerfile`). |
| **Datenhaltung** | K8s PVC & ConfigMap | Sichert die Datenbankzustände persistent ab und injiziert Startkonfigurationen. |

### 1.5 Begründung der gewählten Persistenzstrategie
Um sicherzustellen, dass die Ticketdaten Container-Neustarts überstehen, wurde ein **PersistentVolumeClaim (PVC)** in Kubernetes implementiert. 
**Begründung:** Container sind flüchtig (ephemeral). Durch den PVC wird der Lebenszyklus der persistenten Daten strikt vom Lebenszyklus des Datenbank-Containers entkoppelt. Zudem wird eine **ConfigMap** genutzt, um das initialisierte Datenbankschema (`init.sql`) beim allerersten Start deklarativ und reproduzierbar in den Container zu injizieren.

---

## 2. Deployment-Anleitung (Build- und Run-Schritte)

### 2.1 Automatisches Deployment (Ansible)
Das Projekt nutzt Infrastructure-as-Code. Um das gesamte Setup automatisiert auszuführen, nutzen Sie das Ansible-Playbook:

~~~bash
ansible-playbook -i ansible/hosts.ini ansible/deploy.yml
~~~
*(Hinweis: Minikube muss hierfür bereits laufen)*

### 2.2 Manuelles Deployment (Schritt-für-Schritt)

**Schritt 1: Minikube starten** (als normaler User `devops`, nicht root)
~~~bash
minikube start --driver=docker --cpus=2 --memory=2048
~~~

**Schritt 2: Docker-Image bauen** (im Minikube-Daemon)
~~~bash
eval $(minikube docker-env)
docker build -t ticketshop-app:latest .
eval $(minikube docker-env --unset)
~~~

**Schritt 3: Kubernetes-Ressourcen anwenden**
~~~bash
kubectl apply -f k8s/
~~~

**Schritt 4: Auf Pods warten & Tunnel starten**
~~~bash
kubectl wait --for=condition=ready pod -l app=ticketshop,tier=database -n ticketshop --timeout=120s
kubectl wait --for=condition=ready pod -l app=ticketshop,tier=frontend -n ticketshop --timeout=60s
~~~
Da der Web-Service den Typ `LoadBalancer` nutzt, muss der Tunnel im Vordergrund gestartet werden (Terminal offen lassen):
~~~bash
sudo -E -u devops minikube tunnel
~~~

**Schritt 5: Erreichbarkeit prüfen**
Die Anwendung ist nun unter **http://127.0.0.1:8080** im Browser erreichbar.

---

## 3. Projektdaten & Umgebungsvariablen
Die Datenbank-Verbindungsdaten werden über das Kubernetes `Secret` sicher an den Pod übergeben (siehe `01-secret.yml` & `05-app-deployment.yml`):
* **DB_HOST**: ticketshop-db
* **DB_USER**: ticketuser
* **DB_PASS**: ticketpass
* **DB_NAME**: tickets