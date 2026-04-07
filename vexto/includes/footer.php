    </div> <!-- Cierre de container -->
    <?php if (isLoggedIn()): ?>
        <a href="feedback.php" class="feedback-btn">
            <i class="fas fa-comment-dots"></i> Enviar Feedback
        </a>
    <?php endif; ?>
    <footer style="text-align: center; padding: 2rem; color: #666; font-size: 14px;">
        &copy; <?php echo date('Y'); ?> VEXTO - Red Social. Todos los derechos reservados.
    </footer>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/main_enhanced.js"></script>
</body>
</html>
