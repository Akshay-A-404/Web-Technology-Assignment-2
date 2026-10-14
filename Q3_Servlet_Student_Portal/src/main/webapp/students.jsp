<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List,portal.StudentServlet.Student" %>
<%@ taglib prefix="c" uri="jakarta.tags.core" %>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Profiles</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><div><span class="brand-mark small">SA</span><strong>Student Profile Portal</strong></div>
<form method="post" action="logout"><button class="button secondary" type="submit">Sign out</button></form></header>
<main class="container"><p class="eyebrow">DIRECTORY</p><h1>Student profiles</h1><p class="muted">Profile records are retrieved from MySQL through JDBC.</p>
<% List<Student> students = (List<Student>) request.getAttribute("students"); %>
<% if (students == null || students.isEmpty()) { %><section class="panel"><p>No student records are available.</p></section><% } else { %>
<div class="student-grid">
<% for (Student student : students) { %>
<article class="student-card"><div class="avatar"><%= org.owasp.encoder.Encode.forHtml(student.getName().substring(0,1).toUpperCase()) %></div>
<h2><%= org.owasp.encoder.Encode.forHtml(student.getName()) %></h2>
<p><span>Roll number</span><strong><%= org.owasp.encoder.Encode.forHtml(student.getRollNo()) %></strong></p>
<p><span>Email</span><strong><%= org.owasp.encoder.Encode.forHtml(student.getEmail()) %></strong></p>
<p><span>Course</span><strong><%= org.owasp.encoder.Encode.forHtml(student.getCourse()) %></strong></p>
<%-- Associated files are loaded by the file servlet using file IDs from student_files. --%>
<% try (java.sql.Connection connection = portal.DBUtil.getConnection();
       java.sql.PreparedStatement statement = connection.prepareStatement(
       "SELECT id, original_name, mime_type, category FROM student_files WHERE student_id=? ORDER BY uploaded_at DESC")) {
       statement.setInt(1, student.getId());
       try (java.sql.ResultSet files = statement.executeQuery()) {
         boolean hasFiles = false;
         while (files.next()) {
           hasFiles = true;
           String mime = files.getString("mime_type");
           String fileName = org.owasp.encoder.Encode.forHtml(files.getString("original_name"));
           int fileId = files.getInt("id");
           if (mime != null && mime.startsWith("image/")) { %>
             <img class="profile-image" src="media?id=<%= fileId %>" alt="Uploaded student image">
           <% } %><a class="file-link" href="media?id=<%= fileId %>" target="_blank" rel="noopener"><%= fileName %></a><%
         }
         if (!hasFiles) { %><p class="muted">No uploaded files.</p><% }
       }
    } catch (java.sql.SQLException exception) {
       application.log("Could not list student files", exception); %><p class="muted">Files are temporarily unavailable.</p><% } %>
</article>
<% } %></div><% } %>
</main><footer>Web Technology · Akshay A · 24CS015 · III-CSE-A</footer></body></html>
