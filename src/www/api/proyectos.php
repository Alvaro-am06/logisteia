<?php
/**
 * API REST para gestión de proyectos.
 * Endpoints:
 * - GET: Listar todos los proyectos del jefe autenticado
 * - POST: Crear un nuevo proyecto
 * - PUT: Actualizar proyecto existente
 * - DELETE: Eliminar proyecto
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json; charset=UTF-8');
handlePreflight();

// Cargar dependencias
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Proyecto.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

// Inicializar conexión
try {
    $db = ConexionBBDD::obtener();
    $proyecto = new Proyecto($db);
} catch (Exception $e) {
    logError('Error de conexión en proyectos.php', $e);
    sendJsonError('Error de conexión a la base de datos', 500);
}

// Procesar según el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'];

switch ($method) {
    case 'GET':
        // Obtener jefe_dni de los headers para todas las rutas POST
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        if (!$jefe_dni && isset($_SERVER['HTTP_X_USER_DII'])) {
            $jefe_dni = $_SERVER['HTTP_X_USER_DII'];
        }
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado. Se requiere X-User-DNI header']);
            exit();
        }

        // Ruta: /api/proyectos.php/{id}/trabajadores
        if (preg_match('#/api/proyectos\.php/(\d+)/trabajadores#', $path, $matches)) {
            $proyectoId = intval($matches[1]);
            $trabajadores = $proyecto->obtenerTrabajadoresProyecto($proyectoId);
            echo json_encode([
                'success' => true,
                'trabajadores' => $trabajadores
            ]);
            exit();
        }

        // Ruta: /api/proyectos.php/miembros-disponibles/{equipoId}?proyecto_id={proyectoId}
        if (preg_match('#/api/proyectos\.php/miembros-disponibles/(\d+)#', $path, $matches)) {
            $equipoId = intval($matches[1]);
            $proyectoId = isset($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : null;
            
            $miembros = $proyecto->obtenerMiembrosEquipoDisponibles($equipoId, $proyectoId);
            echo json_encode([
                'success' => true,
                'miembros' => $miembros
            ]);
            exit();
        }

        // Ruta por defecto: Listar proyectos del jefe autenticado
        $proyectos = $proyecto->obtenerProyectosPorJefe($jefe_dni);
        if ($proyectos !== false) {
            echo json_encode([
                'success' => true,
                'proyectos' => $proyectos
            ]);
        } else {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener proyectos']);
        }
        break;

    case 'POST':
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        // Obtener jefe_dni de los headers
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        if (!$jefe_dni && isset($_SERVER['HTTP_X_USER_DII'])) {
            $jefe_dni = $_SERVER['HTTP_X_USER_DII'];
        }
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado. Se requiere X-User-DNI header']);
            exit();
        }

        // Ruta: /api/proyectos.php/{id}/trabajadores - Asignar trabajadores
        if (preg_match('#/api/proyectos\.php/(\d+)/trabajadores#', $path, $matches)) {
            $proyectoId = intval($matches[1]);
            
            error_log("🔵 POST /trabajadores - Proyecto ID: $proyectoId");
            error_log("🔵 Input recibido: " . json_encode($input));
            
            if (!isset($input['trabajadores']) || !is_array($input['trabajadores'])) {
                error_log("❌ Error: No se envió array de trabajadores");
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere un array de trabajadores']);
                exit();
            }
            
            try {
                error_log("🔵 Intentando asignar " . count($input['trabajadores']) . " trabajadores");
                $proyecto->asignarTrabajadores($proyectoId, $input['trabajadores']);
                error_log("✅ Trabajadores asignados correctamente");
                echo json_encode([
                    'success' => true,
                    'message' => 'Trabajadores asignados correctamente'
                ]);
            } catch (Exception $e) {
                error_log("❌ Error asignando trabajadores: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al asignar trabajadores: ' . $e->getMessage()
                ]);
            }
            exit();
        }

        // Ruta: /api/proyectos.php/{id}/asignar-trabajador
        if (preg_match('#/api/proyectos\.php/(\d+)/asignar-trabajador#', $path, $matches)) {
            $proyectoId = intval($matches[1]);
            
            if (!isset($input['trabajador_dni']) || !isset($input['rol'])) {
                http_response_code(400);
                echo json_encode(['error' => 'DNI del trabajador y rol son requeridos']);
                exit();
            }
            
            try {
                $proyecto->asignarTrabajadores($proyectoId, [[
                    'dni' => $input['trabajador_dni'],
                    'rol' => $input['rol']
                ]]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Trabajador asignado correctamente'
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al asignar trabajador: ' . $e->getMessage()]);
            }
            exit();
        }

        // Ruta por defecto: Crear nuevo proyecto
        try {
            if (!$input) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                exit();
            }

            // Validar campos requeridos
            $required_fields = ['nombre', 'descripcion', 'cliente_id'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty(trim($input[$field]))) {
                    ob_end_clean();
                    http_response_code(400);
                    echo json_encode(['error' => "Campo requerido: $field"]);
                    exit();
                }
            }

            // Sanitizar datos
            $nombre = trim($input['nombre']);
            $descripcion = trim($input['descripcion']);
            $cliente_id = intval($input['cliente_id']);
            $tecnologias = isset($input['tecnologias']) ? $input['tecnologias'] : [];
            $fecha_inicio = isset($input['fecha_inicio']) ? trim($input['fecha_inicio']) : null;
            $notas = isset($input['notas']) ? trim($input['notas']) : null;

            // Preparar datos para el modelo
            $datos = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'cliente_id' => $cliente_id,
                'jefe_dni' => $jefe_dni,
                'tecnologias' => $tecnologias,
                'fecha_inicio' => $fecha_inicio,
                'notas' => $notas,
                'precio_total' => isset($input['precio_total']) ? floatval($input['precio_total']) : 0
            ];

            // Crear proyecto usando el método del modelo
            $resultado = $proyecto->crearProyecto($datos);

            if ($resultado && isset($resultado['proyecto_id'])) {
                // Crear presupuesto automáticamente enlazado al proyecto
                $presupuestoCreado = false;
                $presupuestoError = null;
                
                try {
                    $numero_presupuesto = 'PRES-' . date('Ymd') . '-' . str_pad($resultado['proyecto_id'], 4, '0', STR_PAD_LEFT);
                    
                    // Obtener datos del cliente
                    $sqlCliente = "SELECT nombre, email FROM clientes WHERE id = :cliente_id";
                    $stmtCliente = $db->prepare($sqlCliente);
                    $stmtCliente->execute([':cliente_id' => $cliente_id]);
                    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
                    
                    // Preparar notas del presupuesto con datos del wizard
                    $notasPresupuesto = "Presupuesto automático para proyecto: " . $nombre . "\n\n";
                    $notasPresupuesto .= "Cliente: " . ($cliente['nombre'] ?? 'Sin especificar') . "\n";
                    if ($cliente && !empty($cliente['email'])) {
                        $notasPresupuesto .= "Email: " . $cliente['email'] . "\n";
                    }
                    $notasPresupuesto .= "Descripción: " . $descripcion . "\n";
                    
                    // Agregar información del wizard si está disponible
                    if (isset($input['categoriaPrincipal'])) {
                        $notasPresupuesto .= "Categoría: " . $input['categoriaPrincipal'] . "\n";
                    }
                    if (isset($input['tiempoEstimado'])) {
                        $notasPresupuesto .= "Tiempo estimado: " . $input['tiempoEstimado'] . "\n";
                    }
                    if (isset($input['plazoEntrega'])) {
                        $notasPresupuesto .= "Plazo de entrega: " . $input['plazoEntrega'] . "\n";
                    }
                    if (isset($input['prioridad'])) {
                        $notasPresupuesto .= "Prioridad: " . $input['prioridad'] . "\n";
                    }
                    if (isset($input['metodologia'])) {
                        $notasPresupuesto .= "Metodología: " . $input['metodologia'] . "\n";
                    }
                    if (!empty($notas)) {
                        $notasPresupuesto .= "\nNotas: " . $notas;
                    }
                    
                    // Calcular total estimado basado en presupuestoAproximado
                    $totalEstimado = 0.00;
                    if (isset($input['presupuestoAproximado'])) {
                        $rangos = [
                            'menos-1000' => 800,
                            '1000-5000' => 3000,
                            '5000-10000' => 7500,
                            'mas-10000' => 15000
                        ];
                        $totalEstimado = $rangos[$input['presupuestoAproximado']] ?? 0;
                    }
                    
                    // Insertar en tabla presupuestos
                    $sqlPresupuesto = "INSERT INTO presupuestos (numero_presupuesto, usuario_dni, total, validez_dias, notas, fecha_creacion, estado)
                                      VALUES (:numero_presupuesto, :usuario_dni, :total, 30, :notas, NOW(), 'borrador')";
                    $stmtPresupuesto = $db->prepare($sqlPresupuesto);
                    $stmtPresupuesto->execute([
                        ':numero_presupuesto' => $numero_presupuesto,
                        ':usuario_dni' => $jefe_dni,
                        ':total' => $totalEstimado,
                        ':notas' => $notasPresupuesto
                    ]);
                    
                    $presupuesto_id = $db->lastInsertId();
                    
                    // Crear detalles de presupuesto basados en la categoría y tiempo estimado
                    if (isset($input['categoriaPrincipal']) && isset($input['tiempoEstimado'])) {
                        $categoria = $input['categoriaPrincipal'];
                        $tiempo = $input['tiempoEstimado'];
                        
                        // Mapeo de servicios según categoría
                        $serviciosPorCategoria = [
                            'Desarrollo Web' => 'Desarrollo Web Frontend',
                            'Desarrollo Móvil' => 'Desarrollo Móvil',
                            'Base de Datos' => 'Administración Base de Datos',
                            'UI/UX Design' => 'Diseño UI/UX',
                            'Testing' => 'Testing y QA',
                            'DevOps' => 'DevOps y CI/CD',
                            'Infraestructura' => 'Infraestructura Cloud',
                            'Consultoría' => 'Consultoría Técnica',
                            'Mantenimiento' => 'Mantenimiento Web'
                        ];
                        
                        $nombreServicio = $serviciosPorCategoria[$categoria] ?? 'Desarrollo Web Frontend';
                        
                        // Obtener precio del servicio de la base de datos
                        $sqlServicio = "SELECT precio_base FROM servicios_informatica WHERE nombre = :nombre LIMIT 1";
                        $stmtServicio = $db->prepare($sqlServicio);
                        $stmtServicio->execute([':nombre' => $nombreServicio]);
                        $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                        
                        if ($servicio) {
                            $precioHora = $servicio['precio_base'];
                            
                            // Calcular horas según tiempo estimado
                            $horas = 40; // Por defecto
                            $horasPorTiempo = [
                                'menos-1-semana' => 30,
                                '1-2-semanas' => 70,
                                '3-4-semanas' => 140,
                                '1-2-meses' => 250,
                                'mas-2-meses' => 400
                            ];
                            $horas = $horasPorTiempo[$tiempo] ?? 40;
                            
                            $totalDetalle = $horas * $precioHora;
                            
                            // Insertar detalle
                            $sqlDetalle = "INSERT INTO detalle_presupuesto (numero_presupuesto, presupuesto_id, servicio_nombre, cantidad, preci, comentario)
                                          VALUES (:numero_presupuesto, :presupuesto_id, :servicio_nombre, :cantidad, :preci, :comentario)";
                            $stmtDetalle = $db->prepare($sqlDetalle);
                            $stmtDetalle->execute([
                                ':numero_presupuesto' => $numero_presupuesto,
                                ':presupuesto_id' => $presupuesto_id,
                                ':servicio_nombre' => $nombreServicio,
                                ':cantidad' => $horas,
                                ':preci' => $precioHora,
                                ':comentario' => "Estimado para $categoria - $tiempo"
                            ]);
                            
                            // Actualizar total del presupuesto
                            $sqlUpdateTotal = "UPDATE presupuestos SET total = :total WHERE id_presupuesto = :id";
                            $stmtUpdateTotal = $db->prepare($sqlUpdateTotal);
                            $stmtUpdateTotal->execute([
                                ':total' => $totalDetalle,
                                ':id' => $presupuesto_id
                            ]);
                            
                            error_log("Servicio agregado al presupuesto: $nombreServicio ($horas horas x $precioHora€ = $totalDetalle€)");
                        }
                    }
                    
                    // Actualizar proyecto con presupuesto_numero y precio_total
                    $sqlUpdateProyecto = "UPDATE proyectos SET presupuesto_numero = :presupuesto_numero, precio_total = :precio_total WHERE id = :id";
                    $stmtUpdateProyecto = $db->prepare($sqlUpdateProyecto);
                    $stmtUpdateProyecto->execute([
                        ':presupuesto_numero' => $numero_presupuesto,
                        ':precio_total' => $totalEstimado,
                        ':id' => $resultado['proyecto_id']
                    ]);
                    
                    $presupuestoCreado = true;
                    error_log("Presupuesto creado: $numero_presupuesto ($totalEstimado€) para proyecto {$resultado['proyecto_id']}");
                } catch (Exception $e) {
                    $presupuestoError = $e->getMessage();
                    error_log("Error creando presupuesto automático: " . $presupuestoError);
                }

                // Registrar acción administrativa si hay sesión
                session_start();
                if (isset($_SESSION['usuario_dni'])) {
                    try {
                        $accionAdmin = new AccionesAdministrativas();
                        $accionAdmin->registrar(
                            $_SESSION['usuario_dni'],
                            'Creación de proyecto',
                            $resultado['proyecto_id'],
                            "Proyecto creado: $nombre (Cliente ID: $cliente_id)"
                        );
                    } catch (Exception $e) {
                        // No fallar si no se puede registrar la acción
                    }
                }

                ob_end_flush();
                http_response_code(200);
                $response = [
                    'success' => true,
                    'message' => 'Proyecto creado exitosamente',
                    'proyecto' => [
                        'id' => $resultado['proyecto_id'],
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'cliente_id' => $cliente_id
                    ]
                ];
                
                // Incluir información del presupuesto
                if ($presupuestoCreado) {
                    $response['presupuesto_creado'] = true;
                    $response['numero_presupuesto'] = $numero_presupuesto;
                } else if ($presupuestoError) {
                    $response['presupuesto_creado'] = false;
                    $response['presupuesto_error'] = $presupuestoError;
                }
                
                echo json_encode($response);
            } else {
                ob_end_clean();
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear el proyecto']);
            }
        } catch (Exception $e) {
            ob_end_clean();
            error_log("❌ POST proyectos.php EXCEPTION: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al crear proyecto',
                'details' => $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        // Actualizar proyecto existente
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id'])) {
            sendJsonError('ID de proyecto requerido', 400);
        }

        $proyectoId = $input['id'];

        // Obtener jefe_dni de los headers
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit();
        }

        // Actualizar estado si se proporciona
        if (isset($input['estado'])) {
            $sql = "UPDATE proyectos SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id AND jefe_dni = :jefe_dni";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':estado' => $input['estado'],
                ':id' => $proyectoId,
                ':jefe_dni' => $jefe_dni
            ]);

            if ($stmt->rowCount() > 0) {
                // Si se finalizó el proyecto, obtener presupuesto_total para el dashboard
                $presupuesto_total = 0;
                if ($input['estado'] === 'finalizado') {
                    $sqlPresupuesto = "SELECT presupuesto_total FROM proyectos WHERE id = :id";
                    $stmtPresupuesto = $db->prepare($sqlPresupuesto);
                    $stmtPresupuesto->execute([':id' => $proyectoId]);
                    $proyecto = $stmtPresupuesto->fetch(PDO::FETCH_ASSOC);
                    $presupuesto_total = $proyecto['presupuesto_total'] ?? 0;
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Proyecto actualizado correctamente',
                    'presupuesto_total' => $presupuesto_total
                ]);
            } else {
                ob_end_clean();
                http_response_code(404);
                echo json_encode(['error' => 'Proyecto no encontrado o no tienes permisos']);
            }
        } else {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['error' => 'No se proporcionaron campos para actualizar']);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);

        // Obtener jefe_dni de los headers
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit();
        }

        // Ruta: /api/proyectos.php/{id}/trabajadores/{dni} - Remover trabajador
        if (preg_match('#/api/proyectos\.php/(\d+)/trabajadores/([A-Z0-9]+)#', $path, $matches)) {
            $proyectoId = intval($matches[1]);
            $trabajadorDni = $matches[2];
            
            try {
                $resultado = $proyecto->removerAsignacion($proyectoId, $trabajadorDni);
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Trabajador removido correctamente'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al remover trabajador']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            }
            exit();
        }

        // Ruta: /api/proyectos.php/{id}/remover-trabajador
        if (preg_match('#/api/proyectos\.php/(\d+)/remover-trabajador#', $path, $matches)) {
            $proyectoId = intval($matches[1]);
            
            if (!isset($input['trabajador_dni'])) {
                http_response_code(400);
                echo json_encode(['error' => 'DNI del trabajador es requerido']);
                exit();
            }
            
            try {
                $proyecto->removerAsignacion($proyectoId, $input['trabajador_dni']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Trabajador removido correctamente'
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al remover trabajador: ' . $e->getMessage()]);
            }
            exit();
        }

        // Ruta por defecto: Eliminar proyecto
        if (!$input || !isset($input['id'])) {
            sendJsonError('ID de proyecto requerido', 400);
        }

        $proyectoId = $input['id'];

        try {
            // Obtener presupuesto_numero antes de eliminar el proyecto
            $sqlGetPresupuesto = "SELECT presupuesto_numero FROM proyectos WHERE id = :id AND jefe_dni = :jefe_dni";
            $stmtGet = $db->prepare($sqlGetPresupuesto);
            $stmtGet->execute([
                ':id' => $proyectoId,
                ':jefe_dni' => $jefe_dni
            ]);
            $proyecto_data = $stmtGet->fetch(PDO::FETCH_ASSOC);
            
            if (!$proyecto_data) {
                http_response_code(404);
                echo json_encode(['error' => 'Proyecto no encontrado o no tienes permisos']);
                exit();
            }
            
            // Primero eliminar asignaciones de trabajadores (FK constraint)
            $sqlDeleteAsignaciones = "DELETE FROM asignaciones_proyecto WHERE proyecto_id = :proyecto_id";
            $stmtDeleteAsig = $db->prepare($sqlDeleteAsignaciones);
            $stmtDeleteAsig->execute([':proyecto_id' => $proyectoId]);
            
            // Eliminar proyecto
            $sql = "DELETE FROM proyectos WHERE id = :id AND jefe_dni = :jefe_dni";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $proyectoId,
                ':jefe_dni' => $jefe_dni
            ]);

            if ($stmt->rowCount() > 0) {
                // Si el proyecto tenía un presupuesto asociado, eliminarlo
                if ($proyecto_data && !empty($proyecto_data['presupuesto_numero'])) {
                    try {
                        $sqlDeletePresupuesto = "DELETE FROM presupuestos WHERE numero_presupuesto = :numero";
                        $stmtDeletePresupuesto = $db->prepare($sqlDeletePresupuesto);
                        $stmtDeletePresupuesto->execute([':numero' => $proyecto_data['presupuesto_numero']]);
                        error_log("✅ Presupuesto {$proyecto_data['presupuesto_numero']} eliminado junto con proyecto {$proyectoId}");
                    } catch (Exception $presupuestoError) {
                        error_log("⚠️ No se pudo eliminar presupuesto: " . $presupuestoError->getMessage());
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Proyecto eliminado correctamente']);
            } else {
                ob_end_clean();
                http_response_code(404);
                echo json_encode(['error' => 'Proyecto no encontrado o no tienes permisos']);
            }
        } catch (Exception $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar proyecto: ' . $e->getMessage()]);
        }
        break;

    default:
        sendJsonError('Método no permitido', 405);
}
