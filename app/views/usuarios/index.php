<?php
//app/views/usuarios/index.php

// Helper para construir la URL con los parámetros de búsqueda y página
function build_pagination_url($page, $searchTerm) {
    $query_params = ['route' => 'usuarios_index', 'page' => $page];
    if (!empty($searchTerm)) {
        $query_params['search'] = $searchTerm;
    }
    return 'index.php?' . http_build_query($query_params);
}
?>

<div class="page-title-container">
    <h2>Listado de Usuarios</h2>
</div>


<div class="table-header-controls">
    <a href="index.php?route=usuarios/create" class="btn btn-primary">Registrar Nuevo Usuario</a>
    <a href="index.php?route=usuarios_export_excel&search=<?php echo urlencode($searchTerm ?? ''); ?>" class="btn btn-secondary">Exportar a Excel</a>
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="route" value="usuarios_index">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, email, username..." value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>">
        <div class="search-buttons">
            <button type="submit" class="btn btn-secondary">Buscar</button>
            <a href="index.php?route=usuarios_index" class="btn btn-primary">Limpiar</a>
        </div>
    </form>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $searchTerm); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $searchTerm); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $searchTerm); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); } ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Email</th>
                <th>Username</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado en</th>
                <th>Último Login</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($usuarios) && count($usuarios) > 0): ?>
                <?php foreach($usuarios as $usuario): ?>
                    <tr class="clickable-row"
                        data-nombre-completo="<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']); ?>"
                        data-email="<?php echo htmlspecialchars($usuario['email'] ?? '-'); ?>"
                        data-username="<?php echo htmlspecialchars($usuario['username'] ?? '-'); ?>"
                        data-rol="<?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?>"
                        data-estado="<?php echo htmlspecialchars(ucfirst($usuario['estado'])); ?>"
                        data-creado="<?php echo !empty($usuario['created_at']) ? date('d/m/Y H:i', strtotime($usuario['created_at'])) : '-'; ?>"
                        data-ultimo-login="<?php echo !empty($usuario['ultimo_login']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca'; ?>">
                        <td><?php echo $usuario['id_usuario']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?></td>
                        <td>
                            <span style="color: <?php echo ($usuario['estado'] == 'activo') ? 'green' : 'red'; ?>;">
                                <?php echo htmlspecialchars(ucfirst($usuario['estado'])); ?>
                            </span>
                        </td>
                        <td><?php echo isset($usuario['created_at']) ? date('d/m/Y H:i', strtotime($usuario['created_at'])) : '-'; ?></td>
                        <td><?php echo isset($usuario['ultimo_login']) ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca'; ?></td>
                        <td>
                            <?php if ($usuario['id_usuario'] !== $_SESSION['user']['id_usuario']): ?>
                                <div class="action-buttons">
                                    <a href="index.php?route=usuarios/edit&id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                                    <button class="btn btn-danger" onclick="confirmDeactivation(event, <?php echo $usuario['id_usuario']; ?>, '<?php echo htmlspecialchars(addslashes($usuario['username'])); ?>')">
                                        Desactivar
                                    </button>
                                </div>
                            <?php else: ?>
                                <span style="color: #777;">(Usuario actual)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <?php if (!empty($searchTerm)): ?>
                            No se encontraron usuarios que coincidan con "<?php echo htmlspecialchars($searchTerm); ?>".
                        <?php else: ?>
                            No hay usuarios registrados.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($total_pages) && $total_pages > 1): ?>
<nav class="pagination-container">
    <ul class="pagination">
        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page - 1, $searchTerm); ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo build_pagination_url($i, $searchTerm); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo build_pagination_url($page + 1, $searchTerm); ?>">Siguiente</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Estructura HTML de la Ventana Modal para Usuarios -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle">Detalles del Usuario</h2>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14C14.2091 14 16 12.2091 16 10C16 7.79086 14.2091 6 12 6C9.79086 6 8 7.79086 8 10C8 12.2091 9.79086 14 12 14ZM12 16C7.58172 16 4 17.7909 4 20V21H20V20C20 17.7909 16.4183 16 12 16Z"></path></svg>
                    <h4>Información de la Cuenta</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field full-width"><span class="modal-label">Nombre Completo:</span><span class="modal-value" id="modalNombreCompleto"></span></div>
                    <div class="modal-field"><span class="modal-label">Username:</span><span class="modal-value" id="modalUsername"></span></div>
                    <div class="modal-field"><span class="modal-label">Email:</span><span class="modal-value" id="modalEmail"></span></div>
                    <div class="modal-field"><span class="modal-label">Rol:</span><span class="modal-value" id="modalRol"></span></div>
                    <div class="modal-field"><span class="modal-label">Estado:</span><span class="modal-value" id="modalEstado"></span></div>
                </div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2ZM12 4C7.58172 4 4 7.58172 4 12C4 16.4183 7.58172 20 12 20C16.4183 20 20 16.4183 20 12C20 7.58172 16.4183 4 12 4ZM12.5 8V12.5L16 14.25L15.25 15.4L11 13V8H12.5Z"></path></svg>
                    <h4>Actividad</h4>
                </div>
                <div class="modal-grid">
                    <div class="modal-field"><span class="modal-label">Fecha de Creación:</span><span class="modal-value" id="modalCreado"></span></div>
                    <div class="modal-field"><span class="modal-label">Último Inicio de Sesión:</span><span class="modal-value" id="modalUltimoLogin"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el modal de información
    const modal = document.getElementById('infoModal');
    const closeButton = modal.querySelector('.close-button');
    const rows = document.querySelectorAll('.clickable-row');

    // Referencias a los spans del modal
    const modalNombreCompleto = document.getElementById('modalNombreCompleto');
    const modalUsername = document.getElementById('modalUsername');
    const modalEmail = document.getElementById('modalEmail');
    const modalRol = document.getElementById('modalRol');
    const modalEstado = document.getElementById('modalEstado');
    const modalCreado = document.getElementById('modalCreado');
    const modalUltimoLogin = document.getElementById('modalUltimoLogin');

    rows.forEach(row => {
        row.addEventListener('click', function(event) {
            if (event.target.closest('.action-buttons') || this.querySelector('span').textContent.includes('(Usuario actual)')) {
                return;
            }

            // Llenar datos generales del modal
            modalNombreCompleto.textContent = this.dataset.nombreCompleto;
            modalUsername.textContent = this.dataset.username;
            modalEmail.textContent = this.dataset.email;
            modalRol.textContent = this.dataset.rol;
            modalEstado.textContent = this.dataset.estado;
            modalCreado.textContent = this.dataset.creado;
            modalUltimoLogin.textContent = this.dataset.ultimoLogin;
            
            modal.style.display = 'block';
        });
    });

    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});

function confirmDeactivation(event, usuarioId, usuarioName) {
    // Detener la propagación para que no active el modal de la fila
    event.stopPropagation();

    Swal.fire({
        title: '¿Estás seguro?',
        text: `Se desactivará al usuario: ${usuarioName}`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Razón de la desactivación',
        inputPlaceholder: 'Escribe el motivo aquí...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return '¡Necesitas escribir una razón para la desactivación!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `index.php?route=usuarios_delete&id=${usuarioId}`;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'razon';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>