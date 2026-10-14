package portal;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/students")
public class StudentServlet extends HttpServlet {
    public static class Student {
        private final int id;
        private final String name, rollNo, email, course;
        public Student(int id, String name, String rollNo, String email, String course) {
            this.id=id; this.name=name; this.rollNo=rollNo; this.email=email; this.course=course;
        }
        public int getId(){return id;}
        public String getName(){return name;}
        public String getRollNo(){return rollNo;}
        public String getEmail(){return email;}
        public String getCourse(){return course;}
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        List<Student> students = new ArrayList<>();
        String sql = "SELECT id, name, roll_no, email, course FROM students ORDER BY name";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet result = statement.executeQuery()) {
            while (result.next()) {
                students.add(new Student(result.getInt("id"), result.getString("name"),
                    result.getString("roll_no"), result.getString("email"), result.getString("course")));
            }
        } catch (SQLException exception) {
            getServletContext().log("Could not retrieve student records", exception);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Unable to load student records.");
            return;
        }
        request.setAttribute("students", students);
        request.getRequestDispatcher("/students.jsp").forward(request, response);
    }
}
