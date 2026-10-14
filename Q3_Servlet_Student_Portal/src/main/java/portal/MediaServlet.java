package portal;

import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/media")
public class MediaServlet extends HttpServlet {
    // Set this to the same private uploads directory used by the PHP application.
    private Path uploadRoot;

    @Override
    public void init() throws ServletException {
        String configured = getServletContext().getInitParameter("uploadRoot");
        if (configured == null || configured.isBlank()) {
            throw new ServletException("Configure the private uploadRoot context parameter.");
        }
        uploadRoot = Path.of(configured).toAbsolutePath().normalize();
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        String fileId = request.getParameter("id");
        if (fileId == null || !fileId.matches("\\d+")) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "A valid file ID is required.");
            return;
        }
        String sql = "SELECT stored_name, original_name, mime_type FROM student_files WHERE id = ?";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, Integer.parseInt(fileId));
            try (ResultSet result = statement.executeQuery()) {
                if (!result.next()) {
                    response.sendError(HttpServletResponse.SC_NOT_FOUND, "File not found.");
                    return;
                }
                String storedName = result.getString("stored_name");
                String mime = result.getString("mime_type");
                String originalName = result.getString("original_name");
                Path file = uploadRoot.resolve(storedName).normalize();
                if (!file.startsWith(uploadRoot) || !Files.isRegularFile(file)) {
                    response.sendError(HttpServletResponse.SC_NOT_FOUND, "Stored file not found.");
                    return;
                }
                response.setContentType(mime);
                response.setHeader("X-Content-Type-Options", "nosniff");
                response.setHeader("Content-Disposition", "inline; filename=\"" +
                    originalName.replace("\"", "") + "\"");
                response.setContentLengthLong(Files.size(file));
                try (InputStream input = Files.newInputStream(file)) {
                    input.transferTo(response.getOutputStream());
                }
            }
        } catch (SQLException exception) {
            getServletContext().log("Could not retrieve uploaded file", exception);
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "Unable to retrieve file.");
        }
    }
}
