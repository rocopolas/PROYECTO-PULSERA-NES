// Función genérica para configurar drag and drop
function setupDragAndDrop(config) {
    const {
        selectElement,
        unassignedContainer,
        assignedContainer,
        saveButton,
        fetchUrl,
        saveUrl,
        itemType
    } = config;

    let originalState = { unassigned: [], assigned: [] };
    let currentState = { unassigned: [], assigned: [] };

    function loadItems() {
        const selectedId = selectElement.value;
        if (!selectedId) {
            clearContainers();
            return;
        }

        // Construir la URL con el parámetro correcto según el tipo
        const paramName = itemType === 'bracelet' ? 'equipo_id' : 'id';
        fetch(`${fetchUrl}?${paramName}=${selectedId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    originalState = {
                        unassigned: data.unassigned,
                        assigned: data.assigned
                    };
                    currentState = JSON.parse(JSON.stringify(originalState));
                    renderItems();
                }
            });
    }

    function clearContainers() {
        unassignedContainer.innerHTML = '';
        assignedContainer.innerHTML = '';
        saveButton.disabled = true;
    }

    function renderItems() {
        unassignedContainer.innerHTML = '';
        assignedContainer.innerHTML = '';

        currentState.unassigned.forEach(item => {
            const div = createDraggableElement(item);
            unassignedContainer.appendChild(div);
        });

        currentState.assigned.forEach(item => {
            const div = createDraggableElement(item);
            assignedContainer.appendChild(div);
        });

        checkChanges();
    }

    function createDraggableElement(item) {
        const div = document.createElement('div');
        div.className = 'bracelet-item';
        div.draggable = true;
        div.dataset.id = item.id;
        div.textContent = item[itemType === 'bracelet' ? 'alias' : 'nombre'];
        
        div.addEventListener('dragstart', handleDragStart);
        div.addEventListener('dragend', handleDragEnd);
        return div;
    }

    function handleDragStart(e) {
        e.target.style.opacity = '0.4';
        e.dataTransfer.setData('text/plain', e.target.dataset.id);
    }

    function handleDragEnd(e) {
        e.target.style.opacity = '1';
    }

    [unassignedContainer, assignedContainer].forEach(container => {
        container.addEventListener('dragover', e => {
            e.preventDefault();
            container.classList.add('dragover');
        });

        container.addEventListener('dragleave', e => {
            container.classList.remove('dragover');
        });

        container.addEventListener('drop', e => {
            e.preventDefault();
            container.classList.remove('dragover');
            
            const itemId = e.dataTransfer.getData('text/plain');
            const itemElement = document.querySelector(`[data-id="${itemId}"]`);
            
            if (itemElement) {
                const fromAssigned = itemElement.parentElement === assignedContainer;
                const toAssigned = container === assignedContainer;
                
                if (fromAssigned !== toAssigned) {
                    const item = fromAssigned 
                        ? currentState.assigned.find(b => b.id === parseInt(itemId))
                        : currentState.unassigned.find(b => b.id === parseInt(itemId));

                    if (item) {
                        if (fromAssigned) {
                            currentState.assigned = currentState.assigned.filter(b => b.id !== item.id);
                            currentState.unassigned.push(item);
                        } else {
                            currentState.unassigned = currentState.unassigned.filter(b => b.id !== item.id);
                            currentState.assigned.push(item);
                        }
                        
                        currentState.unassigned.sort((a, b) => a[itemType === 'bracelet' ? 'alias' : 'nombre'].localeCompare(b[itemType === 'bracelet' ? 'alias' : 'nombre']));
                        currentState.assigned.sort((a, b) => a[itemType === 'bracelet' ? 'alias' : 'nombre'].localeCompare(b[itemType === 'bracelet' ? 'alias' : 'nombre']));
                        
                        renderItems();
                    }
                }
            }
        });
    });

    function checkChanges() {
        const hasChanges = 
            JSON.stringify(originalState.unassigned.map(b => b.id).sort()) !== 
            JSON.stringify(currentState.unassigned.map(b => b.id).sort());
        
        saveButton.disabled = !hasChanges;
    }

    saveButton.addEventListener('click', () => {
        const selectedId = selectElement.value;
        if (!selectedId) return;

        const changes = {
            [itemType === 'bracelet' ? 'equipo_id' : itemType === 'responsable' ? 'equipo_id' : 'pulsera_id']: selectedId,
            assigned: currentState.assigned.map(b => b.id)
        };

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(changes)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cambios guardados correctamente');
                originalState = JSON.parse(JSON.stringify(currentState));
                checkChanges();
            } else {
                alert('Error al guardar los cambios');
            }
        })
        .catch(() => {
            alert('Error al guardar los cambios');
        });
    });

    selectElement.addEventListener('change', loadItems);
}

document.addEventListener('DOMContentLoaded', function() {
    // Configurar drag and drop para Pulseras
    setupDragAndDrop({
        selectElement: document.getElementById('equipo_id'),
        unassignedContainer: document.getElementById('unassigned-bracelets'),
        assignedContainer: document.getElementById('assigned-bracelets'),
        saveButton: document.getElementById('save-changes'),
        fetchUrl: 'asociar_pulsera_equipo.php',
        saveUrl: 'asociar_pulsera_equipo.php',
        itemType: 'bracelet'
    });

    // Configurar drag and drop para Responsables
    setupDragAndDrop({
        selectElement: document.getElementById('equipo_id_resps'),
        unassignedContainer: document.getElementById('unassigned-responsables'),
        assignedContainer: document.getElementById('assigned-responsables'),
        saveButton: document.getElementById('save-resp-changes'),
        fetchUrl: 'gestionar_responsables.php',
        saveUrl: 'gestionar_responsables.php',
        itemType: 'responsable'
    });

    // Configurar drag and drop para Invitados
    setupDragAndDrop({
        selectElement: document.getElementById('pulsera_id_inv'),
        unassignedContainer: document.getElementById('unassigned-invitados'),
        assignedContainer: document.getElementById('assigned-invitados'),
        saveButton: document.getElementById('save-inv-changes'),
        fetchUrl: 'get_usuarios_invitados.php',
        saveUrl: 'asociar_invitado_pulsera.php',
        itemType: 'invitado'
    });
});
