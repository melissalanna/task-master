// ViewModel: mantém o estado das tarefas, chama a API e atualiza a View.
class TaskViewModel {
    constructor() {
        this.tasks = [];
        this.listElement = document.getElementById('taskList');
        this.formElement = document.getElementById('taskForm');
        this.feedbackElement = document.getElementById('feedback');
        this.countElement = document.getElementById('taskCount');
        this.addButton = document.getElementById('addBtn');

        this.formElement.addEventListener('submit', (event) => {
            event.preventDefault();
            this.addTask();
        });

        // Delegação de eventos: os botões são criados dinamicamente no render().
        this.listElement.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) return;
            const id = Number(button.dataset.id);
            if (button.dataset.action === 'complete') this.completeTask(id);
            if (button.dataset.action === 'delete') this.deleteTask(id);
        });

        this.fetchTasks();
    }

    async request(url, options = {}) {
        const response = await fetch(url, options);
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Não foi possível concluir a operação.');
        return data;
    }

    async fetchTasks() {
        try {
            this.tasks = await this.request('api.php?action=list');
            this.render();
        } catch (error) {
            this.showFeedback(error.message, true);
            this.listElement.innerHTML = '<li class="empty-state">Não foi possível carregar as tarefas. Verifique se o servidor PHP está ativo.</li>';
        }
    }

    async addTask() {
        const formData = new FormData(this.formElement);
        const task = {
            title: String(formData.get('title') || '').trim(),
            description: String(formData.get('description') || '').trim(),
            due_date: String(formData.get('due_date') || ''),
            responsible: String(formData.get('responsible') || '').trim()
        };

        if (!task.title || !task.due_date || !task.responsible) {
            this.showFeedback('Preencha título, data de vencimento e responsável.', true);
            return;
        }

        this.addButton.disabled = true;
        try {
            await this.request('api.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(task)
            });
            this.formElement.reset();
            this.showFeedback('Tarefa adicionada com sucesso.');
            await this.fetchTasks();
        } catch (error) {
            this.showFeedback(error.message, true);
        } finally {
            this.addButton.disabled = false;
        }
    }

    async completeTask(id) {
        try {
            await this.request(`api.php?action=complete&id=${encodeURIComponent(id)}`, { method: 'POST' });
            this.showFeedback('Tarefa marcada como concluída.');
            await this.fetchTasks();
        } catch (error) {
            this.showFeedback(error.message, true);
        }
    }

    async deleteTask(id) {
        if (!window.confirm('Deseja realmente excluir esta tarefa?')) return;
        try {
            await this.request(`api.php?action=delete&id=${encodeURIComponent(id)}`, { method: 'DELETE' });
            this.showFeedback('Tarefa excluída.');
            await this.fetchTasks();
        } catch (error) {
            this.showFeedback(error.message, true);
        }
    }

    render() {
        this.listElement.replaceChildren();
        const pending = this.tasks.filter((task) => Number(task.done) === 0).length;
        this.countElement.textContent = `${this.tasks.length} tarefa(s) · ${pending} pendente(s)`;

        if (this.tasks.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'empty-state';
            empty.textContent = 'Nenhuma tarefa cadastrada. Adicione sua primeira tarefa acima!';
            this.listElement.appendChild(empty);
            return;
        }

        this.tasks.forEach((task) => {
            const item = document.createElement('li');
            item.className = `task-item${Number(task.done) === 1 ? ' done' : ''}`;

            const content = document.createElement('div');
            content.className = 'task-content';
            const title = document.createElement('div');
            title.className = 'task-title';
            title.textContent = task.title;
            content.appendChild(title);

            if (task.description) {
                const description = document.createElement('div');
                description.className = 'task-description';
                description.textContent = task.description;
                content.appendChild(description);
            }

            const meta = document.createElement('div');
            meta.className = 'task-meta';
            const dueDate = document.createElement('span');
            dueDate.textContent = `Vencimento: ${this.formatDate(task.due_date)}`;
            const responsible = document.createElement('span');
            responsible.textContent = `Responsável: ${task.responsible || 'Não informado'}`;
            meta.append(dueDate, responsible);
            content.appendChild(meta);

            const actions = document.createElement('div');
            actions.className = 'task-actions';
            if (Number(task.done) !== 1) {
                actions.appendChild(this.createActionButton('complete', task.id, 'Concluir', 'complete'));
            }
            actions.appendChild(this.createActionButton('delete', task.id, 'Excluir', 'delete'));
            item.append(content, actions);
            this.listElement.appendChild(item);
        });
    }

    createActionButton(action, id, label, extraClass) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `action-button ${extraClass}`;
        button.dataset.action = action;
        button.dataset.id = String(id);
        button.textContent = label;
        return button;
    }

    formatDate(value) {
        if (!value) return 'Não informada';
        const parts = value.split('-');
        return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value;
    }

    showFeedback(message, isError = false) {
        this.feedbackElement.textContent = message;
        this.feedbackElement.className = `feedback ${isError ? 'error' : 'success'}`;
    }
}

// Inicializa a ViewModel quando o HTML já foi carregado.
const vm = new TaskViewModel();
