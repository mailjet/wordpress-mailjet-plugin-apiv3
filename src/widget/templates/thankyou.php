<!doctype html>
<html lang="">
    <head> 
        <title>Subscription</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta charset="UTF-8">
        <meta name="robots" content="noindex,noarchive,nofollow" />
        <link href='http://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
        <style type="text/css">
            body {
                font-family: 'Ubuntu';
                background-color: white;
                padding: 0;
                margin: 0;
            }
            .error {
                text-align: center;
                font-size: 24px;
                font-weight: 700;
                margin-top: 0;
                color: #E2A7A7;
            }
        </style>
    </head>
    <body>

                    <div id="confirmation-page" style="width: 100%; background-color: white;">
    <div class="mj-confirmation-page-header mockup-content paint-area" style="background-color: #e1e1e6; text-align: center;">
        <div style="display: table; height: 90px; width: 100%;">
            <div style="display: table-cell; vertical-align: middle;">
                <div class="mj-confirmation-page-title paint-area paint-area--text" style="font-family:Ubuntu, Helvetica; display: inline-block; text-align: center; font-size: 20px; color: #333333;">
                    <?php echo $newsletterRegistration ?>
                </div>
            </div>
        </div>
    </div>
    <div class="mj-confirmation-page-content mockup-content paint-area" style="text-align: center;">
        <div class="mj-confirmation-page-image-place" style="padding: 50px 0;"><img src="//r.mailjet.com/w/w-confirmation-page-mail.png" alt="confirm subscription"></div>
        <div style="display: table; height: 70px; width: 100%;">
            <div style="display: table-cell; vertical-align: middle;">
                <div class="mj-confirmation-page-text paint-area paint-area--text" style="color: #aab6bd; font-family: Ubuntu, Helvetica; font-size: 22px; display: inline-block;">
                    <b class="medium-b"><?php echo $congratsSubscribed ?></b>
                </div>
            </div>
        </div>
    </div>
</div></p>
        
        
    </body>
</html>