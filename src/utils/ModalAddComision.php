<?php

require_once MODEL_PATH . "UserModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalComision($areaAdscripcion_id)

{
    $modal = "
    <div class=\"modal comision\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Crear comision</h2>
                <button onclick=\"closeModal('comision')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=commissions&action=comision\" method=\"POST\" enctype=\"multipart/form-data\">

                <div class=\"input_group\">
                    <label for=\"empleado\">Empleado</label>
                    <div class=\"select_menu\" id=\"usuario_id_menu\">
                        <div class=\"select_btn\">
                            <span class=\"sBtn_text\">Selecciona al empleado</span>
                            <i class=\"fa-solid fa-chevron-down\"></i>
                        </div>
                        <ul class=\"options\">
                        <li class=\"input_group\">
                            <input type=\"text\" class=\"search_input\" placeholder=\"Buscar empleado...\" />
                        </li>";

                        

                        $db = new DB();
                        $userModel = new UserModel($db);
                        $usersList = $userModel->getUsersList();
                        $areaAdscripcionId = $_SESSION['user_area'];
                        $useName = $_SESSION['user_name'];
                        $userRoleId = $_SESSION['user_role'];
                        
                        
                        foreach ($usersList as $usuario) {
                            if (($userRoleId != 1 && $userRoleId != 2) && $usuario['usuario_nombre'] == $useName) {
                                continue;   
                            }
                            if ($userRoleId == 4 && $usuario['areaAdscripcion_id'] != $areaAdscripcionId) {
                                continue;
                            }
                    
                            $modal .= "<li class=\"option\" data-value=\"" . $usuario["usuario_id"] . "\">
                                           " . (empty($usuario["usuario_foto"]) ? '<img src="assets/images/avatar.png">' : '<img src="data:image;base64,' . base64_encode($usuario['usuario_foto']) . '" >') . "
                                           <span>" . $usuario["usuario_nombre"] . "</span>
                                       </li>";
                        }

    $modal .= "
                        </ul>
                    </div>
                    <input type=\"hidden\" name=\"usuario_id\" id=\"usuario_id\" required>
                </div>

                <!-- CAMPO DE UBICACIÓN CON MAPA -->
                <div class=\"input_group\">
                    <label for=\"lugar\">Lugar</label>
                    <input type=\"text\" id=\"lugar\" name=\"lugar\" required>
                    <button type=\"button\" onclick=\"openMapModal()\">Seleccionar Ubicación en el Mapa</button>
                </div>

                <div id=\"mapModal\" class=\"modal\">
                    <div class=\"modal_content\">
                        <div class=\"modal_header\">
                            <h2>Seleccionar Ubicación</h2>
                            <button onclick=\"closeMapModal()\">Cerrar</button>
                        </div>
                        <div id=\"map\" style=\"height: 400px; width: 100%;\"></div>
                        <button type=\"button\" onclick=\"saveLocation()\">Guardar Ubicación</button>
                    </div>
                </div>

                <div class=\"input_group\">
                    <label for=\"asunto\">asunto</label>
                    <input type=\"text\" id=\"asunto\" name=\"asunto\" required>
                </div>

                <div class=\"input_group\">
                    <label>Viáticos</label>
                    <div class=\"select_menu\" id=\"viaticos_menu\">
                        <div class=\"select_btn\">
                            <span class=\"sBtn_text\">Selecciona</span>
                            <i class=\"fa-solid fa-chevron-down\"></i>
                        </div>
                        <ul class=\"options\">
                            <li class=\"option\" data-value=\"No\">No</li>
                            <li class=\"option\" data-value=\"Si\">Sí</li>
                        </ul>
                    </div>
                    <input type=\"hidden\" name=\"viaticos\" id=\"viaticos\" value=\"No\" required onblur=\"if(this.value === '') this.value = 'No';\">
                </div>

                <div class=\"input_group\" id=\"especificacion_viaticos_field\" style=\"display: none;\">
                    <label for=\"especificacion_viaticos\">Especificación de Viáticos</label>
                    <br>
                    <input type=\"text\" id=\"especificacion_viaticos\" name=\"especificacion_viaticos\"value=\"No\">
                </div>

                <div class=\"input_group\" id=\"observaciones\">
                    <label for=\"observaciones\">Observaciones</label>
                    <br>
                    <input type=\"text\" id=\"observaciones\" name=\"observaciones\">
                </div>


                <div class=\"input_group\">
                    <label for=\"fecha_salida\">Fecha de Salida</label>
                    <input type=\"datetime-local\" id=\"fecha_salida\" name=\"fecha_salida\" required>
                </div>

                <div class=\"input_group\">
                    <label for=\"fecha_regreso\">Fecha de Regreso</label>
                    <input type=\"datetime-local\" id=\"fecha_regreso\" name=\"fecha_regreso\" required>
                </div>

                <div class=\"input_group\">
                    <label>Transporte</label>
                    <div class=\"select_menu\" id=\"transporte_menu\">
                        <div class=\"select_btn\">
                            <span class=\"sBtn_text\">Selecciona</span>
                            <i class=\"fa-solid fa-chevron-down\"></i>
                        </div>
                        <ul class=\"options\">
                            <li class=\"option\" data-value=\"No\">No</li>
                            <li class=\"option\" data-value=\"Si\">Sí</li>
                        </ul>
                    </div>
                    <input type=\"hidden\" name=\"transporte\" id=\"transporte\" value=\"No\" required onblur=\"if(this.value === '') this.value = 'No';\">
                </div>

                <div id=\"transporte_fields\" style=\"display: none;\">
                    <div class=\"input_group\">
                        <label>Transporte Propio</label>
                        <div class=\"select_menu\" id=\"transporte_propio_menu\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">Selecciona</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">
                                <li class=\"option\" data-value=\"No\">No</li>
                                <li class=\"option\" data-value=\"Si\">Sí</li>
                            </ul>
                        </div>
                        <input type=\"hidden\" name=\"transporte_propio\" id=\"transporte_propio\"value=\"No\" >
                    </div>

                    <div class=\"input_group\">
                        <label for=\"marca\">Marca</label>
                        <input type=\"text\" id=\"marca\" name=\"marca\">
                    </div>

                    <div class=\"input_group\">
                        <label for=\"modelo\">modelo</label>
                        <input type=\"text\" id=\"modelo\" name=\"modelo\">
                    </div>

                    <div class=\"input_group\">
                        <label for=\"color\">color</label>
                        <input type=\"text\" id=\"color\" name=\"color\">
                    </div>

                    <div class=\"input_group\">
                        <label for=\"placas\">placas</label>
                        <input type=\"text\" id=\"placas\" name=\"placas\">
                    </div>

                    <div class=\"input_group\" id=\"kilometraje_field\">
                        <label for=\"kilometraje\">kilometraje</label><br>
                        <input type=\"number\" id=\"kilometraje\" name=\"kilometraje\">
                    </div>
                </div>

                <input type=\"hidden\" name=\"status\" id=\"status\">

                <button class=\"insert_comision_btn\">Crear Comision</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Agregar Leaflet.js para el mapa -->
    <link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet/dist/leaflet.css\" />
    <script src=\"https://unpkg.com/leaflet/dist/leaflet.js\"></script>

    <script>
        var map;
        var marker;

        function openMapModal() {
            document.getElementById(\"mapModal\").style.display = \"block\";

            if (!map) {
                map = L.map('map').setView([20.6597, -103.3496], 13); // Coordenadas de CDMX

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                map.on('click', function(e) {
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker(e.latlng).addTo(map);
                });
            }
        }

        function closeMapModal() {
            document.getElementById(\"mapModal\").style.display = \"none\";
        }

        function saveLocation() {
            if (marker) {
                var latlng = marker.getLatLng();
                var lat = latlng.lat;
                var lng = latlng.lng;

                // Llamar a la API de Nominatim para obtener la dirección
                var url = \"https://nominatim.openstreetmap.org/reverse?format=json&lat=\" + lat + \"&lon=\" + lng;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.display_name) {
                            document.getElementById(\"lugar\").value = data.display_name;
                        } else {
                            document.getElementById(\"lugar\").value = \"Ubicación no encontrada\";
                        }
                        closeMapModal();
                    })
                    .catch(error => {
                        console.error(\"Error obteniendo la dirección:\", error);
                        alert(\"No se pudo obtener la dirección. Intenta de nuevo.\");
                    });
            } else {
                alert(\"Por favor selecciona una ubicación en el mapa.\");
            }
        }
        
        function toggleTransporteFields() {
            var transporte = document.getElementById('transporte').value;
            var transporteFields = document.getElementById('transporte_fields');
            transporteFields.style.display = (transporte === 'Si') ? 'block' : 'none';
        }

        function togglekilometrajeField() {
            var transportePropio = document.getElementById('transporte_propio').value;
            var kilometrajeField = document.getElementById('kilometraje_field');
            kilometrajeField.style.display = (transportePropio === 'Si') ? 'none' : 'block';
        }
        
        function toggleViaticosField() {
            var viaticos = document.getElementById('viaticos').value;
            var especificacionViaticosField = document.getElementById('especificacion_viaticos_field');
            especificacionViaticosField.style.display = (viaticos === 'Si') ? 'block' : 'none';
        }

        $(document).ready(function () {
            $(document).on(\"click\", \".select_menu .select_btn\", function () {
                $(this).closest(\".select_menu\").toggleClass(\"active\");
            });

            $(document).on(\"click\", \".options .option\", function (e) {
                e.stopPropagation();

                $(this).closest('.options').find('.option').removeClass('selected');
                $(this).addClass('selected');

                // Se obtiene el texto de la opción; si no tiene <span> o <h3>, se toma el texto directo
                let selectedOption = $(this).find(\"h3, span\").first().text();
                if (!selectedOption) {
                    selectedOption = $(this).text().trim();
                }
                let selectedValue = $(this).data('value');

                $(this).closest(\".select_menu\").find(\".sBtn_text\").text(selectedOption);
                $(this).closest(\".select_menu\").toggleClass(\"active\");

                if ($(this).closest('.select_menu').attr('id') === 'usuario_id_menu') {
                    $('#usuario_id').val(selectedValue); // Asigna el ID del usuario al campo oculto
                } else if ($(this).closest('.select_menu').attr('id') === 'viaticos_menu') {
                    $('#viaticos').val(selectedValue);
                    toggleViaticosField();
                } else if ($(this).closest('.select_menu').attr('id') === 'transporte_menu') {
                    $('#transporte').val(selectedValue);
                    toggleTransporteFields();
                } else if ($(this).closest('.select_menu').attr('id') === 'transporte_propio_menu') {
                    $('#transporte_propio').val(selectedValue);
                    togglekilometrajeField();
                }
            });

            const chips = document.querySelectorAll('.chip');
            const input = document.getElementById('documentType');

            chips.forEach(chip => {
                chip.addEventListener('click', function () {
                    chips.forEach(c => {
                        c.classList.remove('selected');
                    });

                    this.classList.add('selected');
                    input.value = this.getAttribute('data-value');
                });
            });

        });

    document.addEventListener(\"DOMContentLoaded\", function () {
        const searchInput = document.querySelector(\"#usuario_id_menu .search_input\");
        const options = document.querySelectorAll(\"#usuario_id_menu .option\");

        // Filtrado en tiempo real
        searchInput.addEventListener(\"input\", function () {
            const filter = this.value.toLowerCase().normalize(\"NFD\").replace(/[\u0300-\u036f]/g, \"\");

            options.forEach(option => {
                const text = option.textContent.toLowerCase().normalize(\"NFD\").replace(/[\u0300-\u036f]/g, \"\");
                option.style.display = text.includes(filter) ? \"\" : \"none\";
            });
        });
    });

    </script>
    
    ";

    return $modal;
}
