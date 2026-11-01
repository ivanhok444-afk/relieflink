(function () {
    const root = document.querySelector('[data-business-chat]');
    if (!root) {
        return;
    }

    const isAdmin = root.dataset.chatRole === 'admin';
    const csrfToken = root.dataset.csrfToken;
    const list = root.querySelector('[data-chat-conversations]');
    const messages = root.querySelector('[data-chat-messages]');
    const form = root.querySelector('[data-chat-form]');
    const input = root.querySelector('[data-chat-input]');
    const status = root.querySelector('[data-chat-status]');
    const submitButton = form.querySelector('button[type="submit"]');
    let activeConversationId = Number.parseInt(root.dataset.conversationId, 10) || 0;
    let lastMessageId = 0;
    let pollInProgress = false;
    submitButton.disabled = isAdmin && !activeConversationId;

    function setStatus(message, isError) {
        status.textContent = message;
        status.classList.toggle('text-danger', Boolean(isError));
    }

    function addMessage(message) {
        const item = document.createElement('article');
        item.className = message.sender_id === Number(root.dataset.userId)
            ? 'chat-message chat-message-own'
            : 'chat-message';

        const heading = document.createElement('div');
        heading.className = 'chat-message-heading';
        const name = document.createElement('strong');
        name.textContent = message.sender_name;
        const time = document.createElement('time');
        time.dateTime = message.created_at;
        time.textContent = new Date(message.created_at).toLocaleString();
        heading.append(name, time);

        const body = document.createElement('p');
        body.textContent = message.message;
        item.append(heading, body);
        messages.append(item);
        lastMessageId = Math.max(lastMessageId, Number(message.id));
        messages.scrollTop = messages.scrollHeight;
    }

    function clearMessages() {
        messages.replaceChildren();
        lastMessageId = 0;
        submitButton.disabled = isAdmin && !activeConversationId;
    }

    function renderConversations(conversations) {
        if (!list) {
            return;
        }
        list.replaceChildren();
        if (!conversations.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted p-3 mb-0';
            empty.textContent = 'No conversations yet.';
            list.append(empty);
            return;
        }

        conversations.forEach((conversation) => {
            const link = document.createElement('button');
            link.type = 'button';
            link.className = 'chat-conversation' +
                (Number(conversation.id) === activeConversationId ? ' active' : '');
            link.addEventListener('click', () => {
                activeConversationId = Number(conversation.id);
                root.dataset.conversationId = String(activeConversationId);
                clearMessages();
                renderConversations(conversations);
                pollMessages();
            });

            const name = document.createElement('strong');
            name.textContent = `${conversation.first_name || ''} ${conversation.last_name || ''}`.trim()
                || conversation.username;
            const snippet = document.createElement('span');
            snippet.textContent = conversation.last_message || 'No messages yet';
            link.append(name, snippet);

            if (Number(conversation.unread_count) > 0) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-danger';
                badge.textContent = String(conversation.unread_count);
                link.append(badge);
            }
            list.append(link);
        });
    }

    async function getJson(url, options) {
        const response = await fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin',
            ...options
        });
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.error || `Chat request failed (${response.status}).`);
        }
        return result;
    }

    async function pollMessages() {
        if (pollInProgress) {
            return;
        }
        pollInProgress = true;
        try {
            if (isAdmin) {
                const result = await getJson('../../controllers/BusinessChatController.php?action=conversations');
                renderConversations(result.conversations);
                if (!activeConversationId && result.conversations.length) {
                    activeConversationId = Number(result.conversations[0].id);
                    root.dataset.conversationId = String(activeConversationId);
                    renderConversations(result.conversations);
                }
            }

            if (!isAdmin && !activeConversationId) {
                const result = await getJson('../../controllers/BusinessChatController.php');
                activeConversationId = Number(result.conversation_id);
                root.dataset.conversationId = String(activeConversationId);
                result.messages.forEach(addMessage);
                setStatus('Messages refresh automatically while this page is open.', false);
                return;
            }

            if (!activeConversationId) {
                setStatus('Choose a conversation to begin.', false);
                return;
            }

            const requestedConversationId = activeConversationId;
            const query = new URLSearchParams({
                conversation_id: String(requestedConversationId),
                after_id: String(lastMessageId)
            });
            const result = await getJson(
                `../../controllers/BusinessChatController.php?${query.toString()}`
            );
            if (requestedConversationId !== activeConversationId) {
                return;
            }
            result.messages.forEach(addMessage);
            setStatus('Messages refresh automatically while this page is open.', false);
        } catch (error) {
            setStatus(error.message, true);
            console.error('Business chat update failed.', error);
        } finally {
            pollInProgress = false;
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) {
            setStatus('Write a message before sending.', true);
            return;
        }

        const body = new URLSearchParams({ message, csrf_token: csrfToken });
        if (isAdmin) {
            body.set('conversation_id', String(activeConversationId));
        }

        if (isAdmin && !activeConversationId) {
            setStatus('Choose a conversation before replying.', true);
            return;
        }
        submitButton.disabled = true;
        try {
            await getJson('../../controllers/BusinessChatController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: body.toString()
            });
            input.value = '';
            await pollMessages();
        } catch (error) {
            setStatus(error.message, true);
            console.error('Business chat message could not be sent.', error);
        } finally {
            submitButton.disabled = false;
            input.focus();
        }
    });

    pollMessages();
    window.setInterval(pollMessages, 5000);
})();
