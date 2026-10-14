<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Portal Login</title><link rel="stylesheet" href="style.css"></head>
<body class="login-page"><main class="login-card">
<div class="brand-mark">SA</div><p class="eyebrow">STUDENT PORTAL</p><h1>Welcome back</h1><p class="muted">Sign in to view student profiles and documents.</p>
<% if (request.getAttribute("error") != null) { %><div class="alert"><%= org.owasp.encoder.Encode.forHtml(String.valueOf(request.getAttribute("error"))) %></div><% } %>
<form method="post" action="login" class="student-form">
<label>Username<input name="username" required autocomplete="username"></label>
<label>Password<input type="password" name="password" required autocomplete="current-password"></label>
<button class="button" type="submit">Sign in</button>
</form><p class="hint">Demonstration account: student / Student@123</p>
</main></body></html>
