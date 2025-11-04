<div class="whatsapp-container">
    <div class="whatsapp-overlay" id="whatsapp-overlay"></div>
    <div class="whatsapp-button" id="whatsapp-button">
        <img src="https://alibuy.pk/assets/img/whatsapp-fab.png" alt="WhatsApp FAB">
        <div class="notification-dot"></div>
    </div>
    <div class="whatsapp-chat" id="whatsapp-chat">
        <div class="chat-header">
            <div class="profile-info">
                <img src="assets/img/profile-wp.jpg" alt="Profile Image">
                <div>
                    <h3>BeastSMM</h3>
                    <p>Typically replies in minutes</p>
                </div>
            </div>
            <button class="close-chat" id="close-chat">✕</button>
        </div>
        <div class="chat-body" style="background-image: url('https://alibuy.pk/assets/img/what-bg.jpg');">
            <div class="typing-indicator">Typing...</div>
            <div class="chat-bubble" style="display: none;">
                <strong>Hi there 👋</strong>
                <div class="chat-timestamp"></div>
            </div>
            <div class="chat-bubble" style="display: none;">
                How can I help you?
                <div class="chat-timestamp"></div>
            </div>
        </div>
        <div class="chat-footer">
        <a href="https://wa.me/<?=get_option('whatsapp_number')?>/?text=Hello, I have a question" class="chat-now-btn" target="_blank">Chat Now</a>
        </div>
    </div>
</div>



<style>
.whatsapp-container {
    position: fixed;
    bottom: 8px;
    left: 20px;
    z-index: 1000;
}

.whatsapp-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);  /* Dark overlay with 50% opacity */
    transition: opacity 0.4s ease;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.whatsapp-overlay.active {
    display: block;
    opacity: 1;
}

.whatsapp-button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    background-color: #fff;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    position: relative;
    transition: opacity 0.4s ease;
    z-index: 1001;
}

/* Default size for larger screens */
.whatsapp-button img {
    width: 35px; /* Adjust the size as needed */
    height: 35px;

}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .whatsapp-button {
        padding: 1px;
    }

    .whatsapp-button img {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .whatsapp-button {
        padding: 5px;
    background-color: #fff;
        padding: 1px;
    }

    .whatsapp-button img {
        width: 35px;
        height: 35px;
    }
}



.notification-dot {
    position: absolute;
    top: 1px;
    right: 1px;
    width: 10px;
    height: 10px;
    background-color: red;
    border-radius: 50%;
}

.whatsapp-chat {
    display: none;
    width: 300px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    opacity: 0;
    transform: translateY(50px);
    transition: opacity 0.4s ease, transform 0.4s ease;
    z-index: 1002;
}

.whatsapp-chat.open {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #075e54;
    color: #fff;
}

.profile-info {
    display: flex;
    align-items: center;
}

.profile-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}

.chat-header h3 {
    margin: 0;
    font-size: 16px;
}

.chat-header p {
    margin: 0;
    font-size: 12px;
}

.close-chat {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
}

.chat-body {
    padding: 74px 11px;
    font-size: 14px;
    color: #333;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    background-size: cover;
    background-position: center;
}

.typing-indicator {
    font-style: italic;
    color: #999;
    margin-bottom: 10px;
}

.chat-bubble {
    background-color: #dcf8c6;
    padding: 2px 8px;
    border-radius: 10px;
    margin-bottom: 15px;
    max-width: 75%;
    position: relative;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.chat-bubble::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: -10px;
    width: 0;
    height: 0;
    border: 10px solid transparent;
    border-right-color: #dcf8c6;
    border-left: 0;
    margin-top: -10px;
}

.chat-timestamp {
    font-size: 12px;
    color: #999;
    text-align: right;
    margin-top: 5px;
}

.chat-footer {
    padding: 10px;
    background-color: #f1f1f1;
    text-align: center;
}

.chat-now-btn {
    display: inline-block;
    background-color: #25d366;
    color: #fff;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
}

.chat-now-btn:hover {
    background-color: #128c7e;
}

@keyframes slideIn {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(50px);
        opacity: 0;
    }
}

.whatsapp-chat.open {
    animation: slideIn 0.4s forwards;
}

.whatsapp-chat.closing {
    animation: slideOut 0.4s forwards;
}

.fade-in {
    animation: fadeIn 0.8s ease-in-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const whatsappButton = document.getElementById('whatsapp-button');
    const whatsappChat = document.getElementById('whatsapp-chat');
    const whatsappOverlay = document.getElementById('whatsapp-overlay');
    const closeChatButton = document.getElementById('close-chat');
    const typingIndicator = document.querySelector('.typing-indicator');
    const chatMessages = document.querySelectorAll('.chat-bubble');
    const chatTimestamps = document.querySelectorAll('.chat-timestamp');

    // Function to format time as AM/PM
    function formatAMPM(date) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return `${hours}:${minutes} ${ampm}`;
    }

    // Function to update the current time in the chat timestamp every minute
    function updateTimestamp() {
        const now = new Date();
        const timeString = formatAMPM(now);
        chatTimestamps.forEach(timestamp => {
            timestamp.textContent = timeString;
        });
    }

    // Update the timestamp every minute
    setInterval(updateTimestamp, 60000); // Update every 60 seconds

    // Initial timestamp update on page load
    updateTimestamp();

    whatsappButton.addEventListener('click', function() {
        whatsappButton.style.opacity = '0';
        setTimeout(() => {
            whatsappButton.style.display = 'none'; // Hide button smoothly
            whatsappChat.style.display = 'block'; // Ensure chat is displayable
            whatsappOverlay.classList.add('active'); // Show overlay
            whatsappChat.classList.add('open');
        }, 400);
        
        setTimeout(() => {
            typingIndicator.style.display = 'none';  // Hide typing indicator
            chatMessages.forEach(message => {
                message.style.display = 'block';
                message.classList.add('fade-in');  // Apply fade-in effect to each message
            });
        }, 1500); // Delay to simulate typing effect
    });

    closeChatButton.addEventListener('click', closeChat);
    whatsappOverlay.addEventListener('click', closeChat);

    function closeChat() {
        whatsappChat.classList.remove('open');
        whatsappChat.classList.add('closing');
        whatsappOverlay.classList.remove('active'); // Hide overlay
        
        setTimeout(() => {
            whatsappChat.classList.remove('closing');
            whatsappChat.style.display = 'none';
            
            whatsappButton.style.display = 'flex';
            setTimeout(() => whatsappButton.style.opacity = '1', 10); // Show button smoothly
            
            // Reset the chat for next time it's opened
            typingIndicator.style.display = 'block'; // Show typing indicator again for next open
            chatMessages.forEach(message => {
                message.style.display = 'none'; // Hide messages for reset
                message.classList.remove('fade-in');  // Remove fade-in effect class
            });
        }, 400); // Delay to match slide-out animation
    }
});

</script>