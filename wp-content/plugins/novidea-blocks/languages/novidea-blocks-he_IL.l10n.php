<?php
/**
 * Hebrew translations for the novidea-blocks plugin, PHP side (WordPress 6.5+ PHP translation file).
 * Site strings follow src/i18n/ui.ts of the Astro site (Content agent, AGENTS.md §3.5).
 * Block titles/descriptions from block.json use the contexts "block title" / "block description"
 * (key = context + "\4" + English text). Editor (JavaScript) strings: novidea-blocks-he_IL-novidea-blocks-editor.json.
 *
 * TODO(verify): legal review of all consent wording (Privacy Protection Law, Amendment 13).
 *
 * @package novidea-blocks
 */

return array(
	'domain'       => 'novidea-blocks',
	'language'     => 'he_IL',
	'plural-forms' => 'nplurals=2; plural=(n != 1);',
	'messages'     => array(
		// Blocks on the site
		'Coming soon'                    => 'בקרוב',
		'Video coming soon'              => 'הסרטון יעלה בקרוב',
		'Hebrew captions'                => 'כתוביות בעברית',
		"Your browser can't play this video." => 'הדפדפן לא מציג את הסרטון.',
		'Download the video'             => 'להורדת הסרטון',
		'carousel'                       => 'קרוסלה',
		'quote'                          => 'ציטוט',
		'Quote %1$d of %2$d'             => 'ציטוט %1$d מתוך %2$d',
		'Previous quote'                 => 'הציטוט הקודם',
		'Next quote'                     => 'הציטוט הבא',
		'More about Novidea'             => 'עוד על נובידאה',
		'Navigate with Waze'             => 'ניווט ב-Waze',
		'Open in Google Maps'            => 'פתיחה ב-Google Maps',
		'(opens in a new window)'        => '(נפתח בחלון חדש)',
		'Get in touch'                   => 'השאירו פרטים',
		'The contact form will be embedded here' => 'כאן יוטמע טופס יצירת הקשר',
		'The form is coming soon. Form fields:'  => 'הטופס יתווסף בקרוב. שדות הטופס:',
		'The contact form is provided by HubSpot, which uses marketing and form cookies. To show the form, please allow these cookies.' => 'טופס השארת הפרטים מופעל על ידי HubSpot, שמשתמשת בעוגיות שיווק וטפסים. כדי להציג את הטופס, יש לאשר עוגיות אלה.',
		'Allow marketing cookies and show the form' => 'אישור עוגיות שיווק והצגת הטופס',
		'Loading the form…'              => 'הטופס נטען…',
		"We couldn't load the form. An ad blocker may be stopping it. Try refreshing the page or allowing this site." => 'לא הצלחנו לטעון את הטופס. ייתכן שחוסם פרסומות מונע אותו. נסו לרענן את העמוד או לבטל את החסימה לאתר זה.',
		'Please enable JavaScript to see the contact form.' => 'כדי להציג את טופס השארת הפרטים יש להפעיל JavaScript בדפדפן.',

		// Cookie consent
		'This site uses cookies'         => 'האתר משתמש בעוגיות',
		'We use necessary cookies to make the site work. With your consent, we also use cookies for analytics and marketing, including the HubSpot contact form. You can change your choice at any time with the "Cookie settings" link at the bottom of the page.' => 'אנחנו משתמשים בעוגיות הכרחיות כדי שהאתר יפעל. בהסכמתכם נשתמש גם בעוגיות לניתוח השימוש באתר ולשיווק, כולל טופס יצירת הקשר של HubSpot. אפשר לשנות את הבחירה בכל עת בקישור "הגדרות עוגיות" בתחתית העמוד.',
		'Privacy policy'                 => 'למדיניות הפרטיות',
		'Accept all'                     => 'אישור הכול',
		'Necessary cookies only'         => 'רק עוגיות הכרחיות',
		'Settings'                       => 'הגדרות',
		'Cookie settings'                => 'הגדרות עוגיות',
		'Close'                          => 'סגירה',
		'Necessary cookies'              => 'עוגיות הכרחיות',
		'always on'                      => 'תמיד פעילות',
		'Needed for the site to work and to remember your choice.' => 'נדרשות לפעולת האתר ולשמירת הבחירה שלכם.',
		'Analytics cookies'              => 'עוגיות ניתוח',
		'Help us understand how the site is used, so we can improve it.' => 'עוזרות לנו להבין איך משתמשים באתר, כדי לשפר אותו.',
		'Marketing and form cookies'     => 'עוגיות שיווק וטפסים',
		'Used by HubSpot to show the contact form and track enquiries.' => 'משמשות את HubSpot להצגת טופס יצירת הקשר ולמעקב אחר פניות.',
		'Save preferences'               => 'שמירת ההעדפות',

		// SEO
		'Page not found'                 => 'העמוד לא נמצא',
		"The page you're looking for isn't on the Novidea site." => 'העמוד שחיפשתם לא נמצא באתר נובידאה.',
		'The Novidea logo next to a smiling woman in an office' => 'הלוגו של נובידאה ליד אישה מחייכת במשרד',

		// Editor: inserter
		'Novidea sections'               => 'מקטעי נובידאה',

		// Editor: starter page patterns
		'Novidea pages'                  => 'עמודי נובידאה',
		'Home page'                      => 'עמוד בית',
		'Landing page'                   => 'דף נחיתה',
		'Internal page'                  => 'עמוד פנימי',
		'Contact page'                   => 'עמוד יצירת קשר',
		'Legal page'                     => 'עמוד משפטי',
		'Hero, text with video, customer logos and links to the main pages.' => 'פתיח ראשי, טקסט עם סרטון, לוגואים של לקוחות וקישורים לעמודים הראשיים.',
		'Campaign page, the whole story on one page: problem, solution, product, proof, form. Turn on "Keep out of search results".' => 'עמוד קמפיין, כל הסיפור בעמוד אחד: בעיה, פתרון, מוצר, הוכחות וטופס. הפעילו את "הסתרה מתוצאות החיפוש".',
		'Page header, text and feature cards, like "The problem and the solution".' => 'כותרת עמוד, טקסט וכרטיסי יכולות, כמו "הבעיה והפתרון".',
		'Page header, the HubSpot lead form and the office location.' => 'כותרת עמוד, טופס השארת הפרטים של HubSpot ומיקום המשרד.',
		'Page header and numbered legal text, like the privacy policy or the accessibility statement.' => 'כותרת עמוד וטקסט משפטי ממוספר, כמו מדיניות הפרטיות או הצהרת הנגישות.',
		"block title\4Hero"              => 'פתיח ראשי (Hero)',
		"block title\4Page header"       => 'כותרת עמוד',
		"block title\4Text section"      => 'מקטע טקסט',
		"block title\4Feature cards"     => 'כרטיסי יכולות',
		"block title\4Customer logos"    => 'לוגואים של לקוחות',
		"block title\4Page link cards"   => 'כרטיסי קישור לעמודים',
		"block title\4Customer quotes"   => 'ציטוטי לקוחות',
		"block title\4Office location"   => 'מיקום המשרד',
		"block title\4Legal text"        => 'טקסט משפטי',
		"block title\4Lead form (HubSpot)" => 'טופס השארת פרטים (HubSpot)',
		"block description\4Big opening section for the home page and landing pages: picture and slanted yellow panel with the page title and buttons." => 'מקטע פתיחה גדול לעמוד הבית ולדפי נחיתה: תמונה ופאנל צהוב משופע עם כותרת העמוד וכפתורים.',
		"block description\4Opening section for internal pages: picture and slanted yellow panel with the page title (h1) and intro." => 'מקטע פתיחה לעמודים פנימיים: תמונה ופאנל צהוב משופע עם כותרת העמוד (h1) ופתיח.',
		"block description\4Title and paragraphs, with an optional picture or video beside them." => 'כותרת ופסקאות, ולצידן תמונה או סרטון (לא חובה).',
		"block description\4Cards with a brand icon, title and text, in 2 or 3 columns. Cards can be marked \"coming soon\"." => 'כרטיסים עם אייקון, כותרת וטקסט, ב-2 או 3 עמודות. אפשר לסמן כרטיס כ"בקרוב".',
		"block description\4A strip of customers: each shows its logo, or its name as text when there's no logo." => 'שורת לקוחות: לכל לקוח הלוגו שלו, או שמו כטקסט כשאין לוגו.',
		"block description\4Cards that each link to a page: icon, title, short summary and an arrow. 4 per row on desktop." => 'כרטיסים שכל אחד מהם מקשר לעמוד: אייקון, כותרת, תקציר וחץ. 4 בשורה במחשב.',
		"block description\4A carousel of customer quotes with name, role and company. Quotation marks are added automatically." => 'קרוסלת ציטוטי לקוחות עם שם, תפקיד וחברה. המירכאות נוספות אוטומטית.',
		"block description\4Building, address and directions, with buttons that open Waze or Google Maps (no embedded map, so no third-party cookies)." => 'בניין, כתובת והוראות הגעה, עם כפתורים לפתיחה ב-Waze או ב-Google Maps (בלי מפה מוטמעת, ולכן בלי עוגיות צד שלישי).',
		"block description\4Long-form text for legal pages: numbered sections with paragraphs and bullet lists. Links: [text](/page)." => 'טקסט ארוך לעמודים משפטיים: סעיפים ממוספרים עם פסקאות ורשימות. קישורים: [טקסט](/page).',
		"block description\4The HubSpot contact form, loaded only after the visitor allows marketing cookies. Without HubSpot settings it shows a placeholder listing the form fields." => 'טופס יצירת הקשר של HubSpot, שנטען רק אחרי שהגולשים מאשרים עוגיות שיווק. בלי הגדרות HubSpot מוצג מקום שמור עם רשימת השדות.',
	),
);
