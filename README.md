<h1>Old Ajent Email System</h1>

<p>This was a cool prototype designed by Ajent to categorize consumer emails. Each user would register, and be issued an @ajent.com email account. We then had a postfix server set up to pipe all Ajent emails into MongoDb for parsing and rendering (via MailBundle:MessageRenderCommand). This software would manage that whole process and provide a nice GUI for the user to see those emails.</p>

<p>This software (and business model) was later abandoned in favor of a direction, but I posted it here in case somebody finds it useful.</p>

<p>Some notes:</p>

<ul>
<li>You'll see a lot of old Doctrine entities with their fields commented out. This was part of the conversion to MongoDB. The commented out parts represent documentation on the fields used in MongoDB for that entity (but since MongoDB doesn't require an explicitly defined structure, they're provided in the comments using the Doctrine format).</li>
<li>"Oranges" and "Pixonite" folder contains some really old generic classes. I don't really use these anymore, but this was an older prototype so we were just trying to get something working. I wouldn't recommend them for production use.</li>
</ul>

<p>NOTE: Since this is prototype code, it hasn't been fully tested for a production environment. It might have bugs or security issues, so use at your own risk.</p>
