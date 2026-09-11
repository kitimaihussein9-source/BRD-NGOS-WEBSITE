# BRD TANZANIA RESEARCH, CONSULTANCY AND NGO MANAGEMENT SYSTEM

Production deployment instructions are available in [DEPLOYMENT.md](DEPLOYMENT.md). This PHP/MySQL application requires a PHP-compatible host; Vercel can host only a separate static frontend unless the backend is deployed elsewhere.

## 1. Introduction

This project is a web-based information and operations system for Building Resilience to Disasters (BRD Tanzania). It is designed to support the organization’s work in disaster risk management, climate resilience, research, consultancy, training, outreach, and humanitarian service delivery.

The system helps BRD present its mission, services, research capabilities, training opportunities, and requests in a structured digital format. It provides a professional online presence for stakeholders, clients, researchers, trainees, and administrators while improving internal management of content and application workflows.

This project is not a generic NGO website. It is specifically designed around BRD’s operational profile and institutional objectives in research, training, consultancy, community resilience, and disaster management.

---

## 2. Background of the Organization

BRD Tanzania is a non-governmental organization focused on building resilient communities through disaster risk reduction, climate action, research, capacity building, and local engagement. The organization’s work intersects with critical areas such as:

- Disaster Risk Management (DRM)
- Disaster Risk Reduction (DRR)
- Climate change adaptation and mitigation
- Geospatial and Earth observation technologies
- Research and data analysis
- Community outreach and training
- Public awareness and humanitarian support

The website should therefore reflect BRD’s professional identity as a research-driven, community-oriented, and innovation-focused organization.

---

## 3. Problem Statement

BRD currently needs a centralized digital system to communicate its work, manage client requests, present services, and organize operational data. Without such a platform, BRD faces several challenges:

- Fragmented communication across different channels
- Limited visibility of training, consultancy, and research services
- Difficulty tracking client requests and applications
- Lack of structured content management for announcements and programmes
- Poor organization of information for donors, partners, and trainees
- Limited ability to coordinate public and administrative operations through one system

The proposed system resolves these issues by offering a modern website and management portal that supports public information access and internal administration.

---

## 4. Objectives of the System

### 4.1 General Objective
The general objective of this system is to develop a professional BRD Tanzania web platform that supports research, consultancy, training, public communication, admissions, and administrative operations in a secure and efficient manner.

### 4.2 Specific Objectives
1. To provide a professional online platform for BRD Tanzania.
2. To display BRD’s services, research focus areas, and training programmes.
3. To allow clients to submit requests and applications.
4. To support an admission/application process aligned with BRD’s service model.
5. To provide admin and assistant users with a managing dashboard.
6. To improve online visibility and stakeholder engagement.
7. To ensure secure role-based access for different user categories.
8. To create a foundation for future development in volunteer management, GIS, projects, and reports.

---

## 5. Scope of the System

### 5.1 In Scope
- Public home page and organization information
- About BRD section
- Training catalogue
- Consultancy catalogue
- Announcements and updates
- Client request submission and tracking
- Admission/application workflow
- Administrative dashboard
- Role-based authentication and access control
- Content publication by authorized users

### 5.2 Out of Scope
- Full GIS mapping dashboard
- Online payment integration
- Volunteer membership card generation in the current version
- Advanced analytics and donor reporting
- SMS notification system
- Multi-language support

---

## 6. System Users and Actors

The system will involve the following actors:

- Visitor: anyone viewing BRD public pages
- Client: a person or organization seeking BRD services
- Assistant: staff member supporting data and content management
- Administrator: system manager who controls content and requests

---

## 7. Functional Requirements

The system shall provide the following functionalities:

### FR-01: Public Website Access
The system shall allow visitors to access BRD information through a public website.

### FR-02: User Registration and Login
The system shall allow users to register and log in using a username and password. The system shall support role-based access for administrator, client, and assistant users.

### FR-03: Role-Based Access Control
The system shall restrict access to features according to user role. Admins shall be able to manage content and requests, while clients shall access only their own requests and workspace.

### FR-04: Content Management
The system shall allow authorized users to publish announcements, training programmes, and consultancy services.

### FR-05: Training Services Module
The system shall display available training opportunities relevant to disaster resilience, climate adaptation, GIS, and research support.

### FR-06: Consultancy Services Module
The system shall display consultancy categories and service descriptions offered by BRD.

### FR-07: Client Request Submission
The system shall allow clients to submit service requests and programme admission applications with a clear subject and message details.

### FR-08: Admission Management Module
The system shall support an admission/application process that follows a structured workflow:

1. User selects a programme or service
2. User submits application information
3. System validates the request
4. Admin reviews the submission
5. Request is approved, rejected, or sent for clarification
6. User receives a response or next-step instruction

### FR-09: Request Tracking
The system shall provide status tracking so clients can see whether their request is pending, approved, rejected, or under review.

### FR-10: Admin Dashboard
The system shall provide an administrative dashboard showing summary information about content, requests, and system status.

### FR-11: Communication Module
The system shall provide a communication flow between clients and the administrator through service requests and messages.

### FR-12: Session Management
The system shall maintain session-based user authentication and provide logout functionality.

---

## 8. Non-Functional Requirements

### NFR-01: Performance
The system shall respond quickly to normal user actions. Public pages and dashboard views should load within acceptable time limits under normal use.

### NFR-02: Security
The system shall protect user data using secure authentication, password hashing, session validation, and input validation. It shall also restrict unauthorized access to administrative functions.

### NFR-03: Availability
The system shall be available to users most of the time, with scheduled maintenance only when necessary.

### NFR-04: Reliability
The system shall preserve data integrity and continue functionality during expected operational conditions. Errors should be handled gracefully.

### NFR-05: Usability
The system shall be easy to navigate, visually clear, and understandable for non-technical users.

### NFR-06: Maintainability
The project shall be well-structured and modular so updates and future enhancements are easy to implement.

### NFR-07: Scalability
The system shall be designed to support future expansion, such as volunteer management, GIS dashboards, reporting, and additional programme modules.

### NFR-08: Compatibility
The system shall support widely used browsers and be responsive on desktop, tablet, and mobile devices.

---

## 9. BRD Admission and Request Workflow

The admission management model is a major requirement of the system. This makes the platform not only a public website but also a structured service and programme application portal.

### Admission Flow
1. User selects a programme, service, or training category.
2. User submits an application/request form.
3. System stores the application in the database.
4. Administrator reviews the request.
5. Request is approved, rejected, or returned for clarification.
6. Client receives the decision and continues the next process if approved.

### BRD Admission Categories
- Research support admission/application
- Training programme application
- Consultancy request
- Programme inquiry
- General service support request

This structure aligns with BRD’s service-oriented operations and supports a professional application process for clients, trainees, and partners.

---

## 10. System Modules

### 10.1 Public Website Module
This module includes:
- Home page
- About BRD
- Mission and vision
- Service overview
- Training information
- Consultancy information
- Contact section

### 10.2 Content Management Module
This module enables authorized users to manage:
- Announcements
- Training programme content
- Consultancy service content

### 10.3 Client Portal Module
This module allows clients to:
- Submit requests
- Review service options
- Track application statuses
- Access their workspace information

### 10.4 Admin Dashboard Module
This module allows admin users to:
- View all requests
- Review submitted applications
- Approve or reject requests
- Publish new content
- Monitor operational activity

---

## 11. Business Rules

1. Only authorized users may publish BRD content.
2. Only registered users may submit service or admission requests.
3. Admin users must review and approve or reject submissions.
4. Every request must contain a subject and a message.
5. Role-based access must be enforced for all protected areas.
6. Sensitive information should not be publicly visible.
7. Content published on the platform must comply with BRD’s mission and professional standards.

---

## 12. Technical Architecture

The project uses a lightweight PHP and MySQL architecture with a user-friendly front-end.

### Front-end
- HTML
- CSS
- JavaScript
- Responsive user interface

### Back-end
- PHP
- PDO database access
- Session management
- Role-based logic

### Database
- MySQL
- Tables for users, content, and requests

### Deployment Environment
- XAMPP
- Apache Web Server
- MySQL Database Server

---

## 13. Data and Database Requirements

The system will primarily maintain the following entities:

- Users
- Roles
- Content
- Requests
- Admission applications
- Operational records

These data structures support the platform’s core functions: user access, public information publishing, and request management.

---

## 14. Security Requirements

The system must include security controls such as:

- Password hashing
- User session control
- Access restriction by role
- Input validation
- Secure database handling
- Protection against common web threats
- Controlled file access and upload procedures

---

## 15. Usability Requirements

The interface should be:

- Clear and easy to navigate
- Responsive on multiple devices
- Simple for both public users and administrators
- Designed to support communication and service requests without confusion

---

## 16. Significance of the Project

This system is important because it gives BRD Tanzania a modern digital platform that strengthens communication, improves service visibility, and organizes operational processes. It supports BRD’s mission of building resilient communities by allowing the organization to present its work professionally and manage service requests more effectively.

---

## 17. Current Implementation Status

The project already includes the foundation for:

- Public BRD website
- Client access and request workflow
- Admin and assistant roles
- Content publishing
- Request tracking
- Database-driven operations

This makes it a strong starting point for a more advanced BRD digital system.

---

## 18. Recommended Future Enhancements

To align the project with BRD’s long-term development goals, future modules could include:

- Volunteer registration and approval system
- Training applicant management
- Event calendar and outreach planning
- Project portfolio management
- Publications and reports section
- GIS and map-based information pages
- Donation and partnership support pages
- Email notification system
- Advanced reporting and analytics

---

## 19. Conclusion

The BRD Tanzania digital platform is a professional NGO information and management system designed around the organization’s real needs in disaster resilience, research, consultancy, training, and public engagement. The system includes a structured admission/application workflow, content management, role-based access, and operational request tracking, making it suitable as a functional foundation for BRD’s online presence and internal processes.

This project is therefore relevant, practical, and aligned with the organization’s mission and goals.

---

## 20. Local Setup

1. Put the project in the XAMPP folder as: C:\xampp\htdocs\brd
2. Start Apache and MySQL in XAMPP
3. Open the project in a browser using:

   http://localhost/brd/index.php

4. Use the default administrator account:

```text
Username: admin
Password: admin123
```

---

## 21. Database Configuration

Default local database settings:

```text
Host: 127.0.0.1
Port: 3306
Database: brd_ngos
Username: root
Password: empty
```

Optional environment variables:

```text
BRD_DB_HOST
BRD_DB_PORT
BRD_DB_NAME
BRD_DB_USER
BRD_DB_PASSWORD
```

---

## 22. Project Structure

- index.php - public landing page
- auth.php - authentication and session logic
- catalog.php - content catalogue
- client.php - client portal
- dashboard.php - role-based dashboard
- manage.php - admin and assistant operations
- db.php - database connection and setup
- public_content.php - content feed and retrieval
- styles.css - shared styling
- script.js - client-side behavior
- uploads/ - file uploads directory

---

## 23. Summary

The BRD project is a suitable digital platform for an NGO that works in research, consultancy, capacity building, and disaster resilience. It reflects BRD’s real identity and operational needs, especially in admissions, service requests, programme promotion, and internal administration.

The current implementation provides a strong foundation for a professional BRD website and can be expanded into a more advanced NGO management system in future phases.
