"
    <html>
    <head>
      <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container {
          background-color: #f4f4f4;
          padding: 20px;
          border-radius: 8px;
          max-width: 500px;
          margin: auto;
        }
        .otp {
          font-size: 28px;
          font-weight: bold;
          color: #4CAF50;
          margin: 20px 0;
        }
        .footer {
          font-size: 12px;
          color: #888;
          margin-top: 20px;
        }
      </style>
    </head>
    <body>
      <div class='container'>
        <h2>Welcome to Shakti AI</h2>
        <form method="post">

          <textarea id="messageInput" class="message-input" placeholder="Type your message....." rows="1" name="demo"></textarea>

        </form>

      </div>
      
      <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $demo = $_POST['demo'];

      }
          
      
      ?>
    </body>
    </html>
