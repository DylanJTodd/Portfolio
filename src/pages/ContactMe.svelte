<script lang="ts">
    import { navigateTo } from '../stores/routeStore';
  
    let senderName = '';
    let senderEmail = '';
    let phoneNumber = '';
    let subject = '';
    let messageContent = '';
    let errorMessage = '';
    let successMessage = '';
  
    async function handleSubmit(event: Event) {
      event.preventDefault();
      errorMessage = '';
      successMessage = '';
  
      if (!senderName || !senderEmail || !messageContent) {
        errorMessage = 'Please fill out all required fields.';
        return;
      }
  
      try {
        const response = await fetch('/api/messages', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            sender_name: senderName,
            sender_email: senderEmail,
            phone_number: phoneNumber || null,
            subject: subject || null,
            message_content: messageContent
          })
        });
  
        if (response.ok) {
          successMessage = 'Your message has been sent successfully!';
          senderName = '';
          senderEmail = '';
          phoneNumber = '';
          subject = '';
          messageContent = '';
        } else {
          const data = await response.json();
          errorMessage = data.error || 'Failed to send your message.';
        }
      } catch (error) {
        console.error('Error sending message:', error);
        errorMessage = 'An error occurred while sending your message.';
      }
    }
  </script>
  
  <section class="contact-form">
    <p style="margin-top: 0; margin-bottom: 5px;">Please fill out the form below to send me a message. Fields marked with * are required.</p>
  
    {#if errorMessage}
      <p class="error-message">{errorMessage}</p>
    {/if}
  
    {#if successMessage}
      <p class="success-message">{successMessage}</p>
    {/if}
  
    <form on:submit={handleSubmit}>
      <div class="form-grid">
        <label for="senderName">Name *</label>
        <input
          type="text"
          id="senderName"
          bind:value={senderName}
          required
          placeholder="Your Name"
        />
  
        <label for="senderEmail">Email *</label>
        <input
          type="email"
          id="senderEmail"
          bind:value={senderEmail}
          required
          placeholder="Your Email"
        />
  
        <label for="phoneNumber">Phone Number</label>
        <input
          type="tel"
          id="phoneNumber"
          bind:value={phoneNumber}
          placeholder="Your Phone Number"
          pattern="^(\\+?\\d{1,3}[-.\\s]?)?(\\(?\\d{3}\\)?[-.\\s]?)?\\d{3}[-.\\s]?\\d{4}$"
          title="Please enter a valid phone number."
        />
  
        <label for="subject">Subject</label>
        <input
          type="text"
          id="subject"
          bind:value={subject}
          placeholder="Subject"
        />
  
        <label for="messageContent">Message *</label>
        <textarea
          id="messageContent"
          bind:value={messageContent}
          required
          placeholder="Your Message"
          rows="5"
        ></textarea>
      </div>
      <button type="submit">Send Message</button>
      <button type="button" on:click={() => navigateTo('navigation')}>Return</button>
    </form>
  </section>