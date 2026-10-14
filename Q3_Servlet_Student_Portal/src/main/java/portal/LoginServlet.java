package portal;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        String username = request.getParameter("username");
        String password = request.getParameter("password");
        if (username == null || password == null || username.isBlank() || password.isBlank()) {
            request.setAttribute("error", "Enter both username and password.");
            request.getRequestDispatcher("/login.jsp").forward(request, response);
            return;
        }

        // Demo-only credentials. Replace with a users table and password hashing in production.
        if ("student".equals(username) && "Student@123".equals(password)) {
            HttpSession session = request.getSession(true);
            session.setAttribute("user", username);
            session.setMaxInactiveInterval(20 * 60);
            response.sendRedirect(request.getContextPath() + "/students");
            return;
        }
        request.setAttribute("error", "Invalid login details.");
        request.getRequestDispatcher("/login.jsp").forward(request, response);
    }
}
