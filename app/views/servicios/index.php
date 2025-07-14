
<!-- app/views/servicios/index.php  -->

<h2>Listado de Servicios Solicitados</h2>

<a href="index.php?route=servicios/create" class="btn btn-primary margin-bottom-15">Registrar Nuevo Servicio</a>

<form action="index.php" method="GET" class="filter-form">
     <input type="hidden" name="route" value="servicios_index">
     <div class="filter-controls">
         <div>
             <label for="filtro_estado" class="filter-label">Estado:</label>
             <select name="filtro_estado" id="filtro_estado" class="form-control">
                  <option value="">-- Todos --</option>
                 <?php
                   $estadosPosiblesFiltro = ['Pendiente Docs/Pago', 'Recibido Completo', 'Pendiente Visita Medico', 'Pendiente Resultado Lab', 'Enviado a LG', 'Pendiente Respuesta LG', 'Completado', 'Rechazado', 'Cancelado'];
                   $estadoSeleccionado = $_GET['filtro_estado'] ?? '';
                   foreach ($estadosPosiblesFiltro as $est) {
                       echo "<option value=\"$est\"" . ($estadoSeleccionado === $est ? ' selected' : '') . ">$est</option>";
                   }
                 ?>
             </select>
         </div>
         <div>
              <label for="filtro_socio_id" class="filter-label">Socio:</label>
              <select name="filtro_socio_id" id="filtro_socio_id" class="form-control filter-select">
                  <option value="">-- Todos --</option>
                  <?php
                   $socioSeleccionado = $_GET['filtro_socio_id'] ?? '';
                   $sociosList = $sociosList ?? [];
                   foreach($sociosList as $id => $display) {
                       echo "<option value=\"$id\"" . ($socioSeleccionado == $id ? ' selected' : '') . ">" . htmlspecialchars($display) . "</option>";
                   }
                  ?>
              </select>
         </div>
          <div>
                <label for="filtro_tipo_id" class="filter-label">Tipo Servicio:</label>
                <select name="filtro_tipo_id" id="filtro_tipo_id" class="form-control filter-select">
                      <option value="">-- Todos --</option>
                      <?php
                       $tipoSeleccionado = $_GET['filtro_tipo_id'] ?? '';
                       $tiposServicioList = $tiposServicioList ?? TipoServicio::getActiveForSelect();
                       foreach($tiposServicioList as $id => $display) {
                           echo "<option value=\"$id\"" . ($tipoSeleccionado == $id ? ' selected' : '') . ">" . htmlspecialchars($display) . "</option>";
                       }
                       ?>
                </select>
          </div>
         <div>
              <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
              <a href="index.php?route=servicios_index" class="btn btn-light btn-sm">Limpiar</a>
          </div>
     </div>
</form>


<?php
// Mensajes de sesión
if(isset($_SESSION['message'])){ echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>"; unset($_SESSION['message']); }
if(isset($_SESSION['error'])){ echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>"; unset($_SESSION['error']); }
if(isset($_SESSION['warning'])){ echo "<div class='alert alert-warning'>" . $_SESSION['warning'] . "</div>"; unset($_SESSION['warning']); }
?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Tipo Servicio (Código)</th>
                <th>Socio (Cód. Gan.)</th>
                <th>Ejemplar (Registro)</th>
                <th>Estado</th>
                <th>Fecha Solicitud</th>
                <th>Últ. Modif.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php $servicios = $servicios ?? []; ?>
            <?php if(count($servicios) > 0): ?>
                <?php foreach($servicios as $servicio): ?>
                    <tr>
                        <td><?php echo $servicio['id_servicio']; ?></td>
                        <td><?php echo htmlspecialchars($servicio['tipo_servicio_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['codigo_servicio'] ?: 'N/A'); ?>)</td>
                        <td>
                            <?php echo htmlspecialchars($servicio['socio_apPaterno'] . ' ' . $servicio['socio_apMaterno'] . ', ' . $servicio['socio_nombre']); ?>
                            (<abbr title="Código Ganadero"><?php echo htmlspecialchars($servicio['socio_codigo_ganadero'] ?? 'S/C'); ?></abbr>)
                        </td>
                        <td>
                            <?php if(!empty($servicio['ejemplar_id'])): ?>
                                <?php echo htmlspecialchars($servicio['ejemplar_nombre'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($servicio['ejemplar_registro'] ?? 'N/R'); ?>)
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(['/', ' '], ['-', '-'], $servicio['estado'])); ?>">
                                <?php echo htmlspecialchars($servicio['estado']); ?>
                            </span>
                        </td>
                        <td><?php echo isset($servicio['fechaSolicitud']) ? date('d/m/Y', strtotime($servicio['fechaSolicitud'])) : '-'; ?></td>
                        <td><?php echo isset($servicio['fecha_modificacion']) ? date('d/m/Y H:i', strtotime($servicio['fecha_modificacion'])) : '-'; ?> por <?php echo htmlspecialchars($servicio['modificador_username'] ?? 'Sistema'); ?></td>
                        <td>
                            <a href="index.php?route=servicios/edit&id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-warning btn-sm">Ver/Editar</a>
                            <?php if (!in_array($servicio['estado'], ['Cancelado', 'Completado', 'Rechazado'])): ?>
                                <a href="index.php?route=servicios_cancel&id=<?php echo $servicio['id_servicio']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro de CANCELAR servicio #<?php echo $servicio['id_servicio']; ?>?')">Cancelar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No hay servicios registrados<?php echo !empty($filters) ? ' que coincidan con los filtros aplicados' : ''; ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>