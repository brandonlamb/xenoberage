<?php
include "config/config.php";
$title = "Blacknova Traders FAQ";
$body_class = 'faq';
if (!isset($_GET['lang']))
{
    $_GET['lang'] = null;
    $lang = $default_lang;
    $link = '';
}
else
{
    $lang = $_GET['lang'];
    $link = "?lang=" . $lang;
}
include "header2.php";
?>
<div class="xenobe-container">
	<div class="header-image"></div>
<?
global $l; // The language object
?>
	<div class="faq-container">
        <div class="faq-links">
        	<a href='index.php'>Home</a> - <a href="faq.php#guide">New Player Guide</a> - <a href="faq.php#new">The Rules</a> - <a href="faq.php#strategies">Strategies</a> - <a href="faq.php#misc">Misc</a>
    	</div>
        	<h2>Introduction</h2>
            <p>Welcome to the Xenobe Rage FAQ Page. Here you can get all the help you need to play the game and ultimatly defend your empire against the rage of the Xenobes!</p>
        	<a id="new"></a><hr class="dotted" /></hr>
            <h2>Rules</h2>
        	<ul>
            	<li>NO Multiple Accounts.</li>
                <div class="faq-message"><div class="faq-content">There are systems in place to detect multiple account, as this is considered cheating. If you need to make a new account for any reason, destroy your old ship first... use the self destruct, or attack somthing with it (As long as you have no escape pod).</div></div>
                <li>NO use of external scripts to gain advantage inside the game.</li>
                <div class="faq-message"><div class="faq-content">Using any browser side hacks or scripts to gain an advantage in the game will just lead to a straight Ban!</div></div>
                <li>No inheritance exploits. This is also known as snowballing.</li>
                <div class="faq-message"><div class="faq-content">So, no creating an account, using up all your turns to get a load of credits, dumping them on a planet and then creating another account to take those credits. This is cheating and just plain lame!</div></div>
                <li>Keep the use of profanity to a minumin.</li>
                <div class="faq-message"><div class="faq-content">Using foul language on planet names or in sector beacons, is a definite NO! However if you need to vent your rage at a specific player, please do it in a constructive way, drop a mine field in one of their sectors!</div></div>
				<li>Never Exploit Bugs!</li>
                <div class="faq-message"><div class="faq-content">If you discover a bug in the game, dont exploit it, report It. There are systems in place to detect when a player has done somthing impossible (i.e. expoited a bug) and our first action will be just to ban, then query! So to keep your empire safe, its better to report any bugs your encounter!</div></div>
            </ul>
            <p>If you have any other questions you'd like to ask, ask on the forums, or contact the admin team!</p>
		<a id="guide"></a><hr class="dotted" /></hr>
		<h2>Player Guide</h2>
			<h3>The Basics</h3>
            <p>You start with an initial number of turns, and every server tick, you gain more turns in which to play with!</p>
            <div class="faq-message"><h1>Current Server</h1><div class="faq-content">Server currently set to give <span class="faq-hightlight-text"><?php echo $turns_per_tick; ?></span> turns every <span class="faq-hightlight-text"><?php echo $sched_turns; ?></span> minutes</div></div>
			<p>You can navigate through the universe using either of 2 methods. The basic method is warp links.</p><p>Warp links are like gateways between two different sectors in the universe. Regardless of the linear distance between 2 points, a warp link will always only take 1 turn. Generally, consecutively numbered sectors will have a link between them, but this is not always the case.</p><p>The second method is real space movement. Using real space, you use your ships engines to move between points in the universe. The bigger your engines, the faster you can go and so the quicker you can move between points in the universe and therefore the less turns you use. Initially, your engines will be low powered, so moving between sectors will take a huge number of turns, so it is not worth using real space movement early in the game.
</p><div class="faq-message"><div class="faq-content">It's important to never take a link without making sure that you can come back from the new sector, unless your realspace engines are big enough to get you back efficiently. There are a lot of one way links in this game. Try to stay away from them. Also, write down everywhere you go. That way you can get back to sol when it's time to upgrade. Or alternatively use your onboard computer to calculate a path to a sector if you havent written your path down!</div></div>
<ul>			
<li>Special ports sell upgrades for your ships. Sector 0 is always a special port. Write down any other special ports you find.</li>
<li>Different regions of the galaxy are governed by different rules. Federation space prevents any form of combat, so new players are safe in Federation space. You can tell what region you are in by looking in the top right corner of your screen.</li>
<li>Try to find a goods or an ore port. Scan each sector from sol. If there isn't one, move to sector 1 and keep trying. As soon as you find an ore or a goods port, move there and trade.</li>
<li>Now try to find an opposing port adjacent to the port you are in. In other words, if you first found a goods port, find an ore port next to it. The important thing is to find two adjacent sectors with ore and goods ports close to sol. This step may take anywhere from a couple to a bunch of turns. I know that's vague, but the layout of the ports changes every turn. The closer to sol you find the ports, the better.</li>
<li>Trade back and forth between these ports until you can afford an upgrade. At this point, go back to sol and upgrade your hull. The bigger your hull, the more cargo you can carry and therefore the more money you can make in each turn. Go back to the sectors you found and start trading again.</li>
<li>Keep doing this until you have a spare 100k credits. Use it to buy an escape pod. Keep trading and upgrading your hull.</li>
<li>When you have a spare million, buy an Emergency Warp Device. Emergency Warp devices will move your ship to a random sector if you are attacked. Keep upgrading your hull. You should be relatively unkillable at this point. Emergency warp devices become unreliable though when your hull reaches size 15.</li>
<li>When you have the cash, buy more EWDs. For every EWD you buy, also get a warp editor. That way, if you are attacked, you can create a one way link back to sol (sector 0) and use it. You can't be stranded in the middle of nowhere. This becomes un-necessary when your real space engines are large enough.</li>
<li>Use traderoutes. Traderoutes help automate the task of trading. You can get your ship computer to move between 2 ports and trade the commodoties without you having to issue commands to move, trade etc. It still takes the same number of turns, but requires less work from you. Traderoutes can work on either real space or warp links. When you first start out, you will want to use warp links, so find sectors that are linked by warp links to trade between. Traderoutes can be one way or two ways. A two way traderoute means your ship will buy commodities from port A, sell them at port B, buy from port B then go back to port A and sell.</li>
<li>Sector defences consist of mines and fighters. Mines are deployed torpedoes. Mines can only detect an incoming ship with a hull size greater than a certain level. Usually 8. Fighters can be set to one of two modes. Attack or Toll. In attack mode, they will attack any ship that does not belong to their owner or a member of their owners team. In toll mode, they will only let you enter the sector safely if you agree to pay a toll. Sector fighters require energy from a friendly planet to remain in the sector. If there is insufficient or no energy, they will slow break down. A defence against mines are mine deflectors. It is a good idea to carry a lot of these. They are cheap anyway. With fighters, you are given the options of fighting, retreating or using your ships cloaking device to try and sneak in to the sector. Sector fighters require energy from a friendly planet in the same sector, otherwise they begin to degrade. The default amount of energy required is 1 unit of energy per 10 ships. Energy can be taken from any of your planets or from a corporate planet from your team in that sector.</li>
<li>Planets can created using a genesis torpedo. Planets can produce commodoties and credits to fund your ship. The more colonists you have, the more they produce. You can use traderoutes to populate your planets from special ports.</li>
 </ul>           
            
            
        <a id="strategies"></a><hr class="dotted" /></hr>
		<h2>Strategies</h2>
            <p>There are many different and generic ways to play the game. Below is a list of the most common types:
            <h3>The Trader</h3>
            	<p>The Trader primarily spends his time trading. The best thing to do is find a goods port and an ore port in adjacent sectors. Trade back and forth until either you can afford a hull upgrade or the port's prices are no longer very good. Keep doing this. When you're engines are large enough to realspace (this varies on the galaxy size in each game, usually anywhere from 14 to 18) start doing trade routes between <span class="faq-hightlight-text">goods</span> and <span class="faq-hightlight-text">ore</span> ports. They don't have to be adjacent at this point. Be sure to buy a fuel scoop if you're going to realspace trade (trade route). </p><p>Be sure to have the maximum amount of EWDs and an escape pod at all times to ensure survival. You don't have to upgrade any techs except for hull, energy, and engines. Everything else is good for combat or colonizing. Your military techs can be zero as the EWDs are your primary means of survival.</p>
            	<div class="faq-message"><h1>PROS</h1><div class="faq-content">Quick rise in score. Good to play catch up if you enter the game late.</div></div>
				<div class="faq-message"><h1>CONS</h1><div class="faq-content">Lack of planetary empire means that you'll lose out in the long run. I find that the Trader is only effective up to about a hull level of 18 or so. That's just my gut reaction. It might be wrong. It's probably a lower tech level in reality.</div></div>
            <h3>The Builder</h3>
				<p>The Builder is mainly concerned in building a planetary empire. As such, he should build his hull to a level 15-16. Then start colonizing a planet. Colonize planets to about 25-50 million before moving on to the next planet. The reason for not fully colonizing a planet is that you want the colonists to procreate for as long as possible. They stop when there are 100 million people on a planet. I guess sex is boring at that point. Didn't think that was possible. My bad. :)</p><p>Here's the deal on upgrading. When you hit a 15-16 hull level, upgrade everything to within 4 of your hull. Actually, forget about sensors. Builders don't need sensors. They don't need amour either for that matter. <span class="faq-hightlight-text">Always have full EWDs</span> and an <span class="faq-hightlight-text">escape pod</span>. Every time you upgrade your hull, upgrade the other techs. Quit upgrading engines when you can realspace anywhere in 1 turn. Upgrade as soon as you can. </p><p>So far as colonizing is concerned, realspace to a special port. Pick up a full load of colonists, fighters, and torps. Realspace to your new planet. Drop off colonists, fighters, torps, and the energy you made from realspacing. On each new planet, set the energy production to 5% and all other productions to zero. You'll need the energy to power planetary shields and beams. </p><p>Colonize constantly. Use the money made by your planets to buy the stuff to supply your planets. You don't really need to trade much in this strategy. </p>
				<div class="faq-message"><h1>PROS</h1><div class="faq-content">You make lots of cash in the long run.</div></div>
				<div class="faq-message"><h1>CONS</h1><div class="faq-content">Kind of slow to start. Conquerors can sometimes take your planets. </div></div>
            <h3>The Banker</h3>
				<p>The Banker builds one planet to full capacity. Upgrade as though you were a builder. Be sure that the planet is completely well defended. Keep adding fighters. If you think that the planet has a ridiculously high number of fighters, then it's probably the right number. I'd recommend spending something like 5-10% of your turns adding more fighters and torps to the planet. </p><p>Ok, here's the way the Banker makes his living. Put all your money on the planet and then land on the planet. It should be well defended enough to survive any attacks. Wait 600 turns, during which the money will earn interest. Play the 600 turns as though you were a Trader. At the end, put the new money on the planet and wait another 600 turns before you play again. The important thing is to let the money sit around and accrue interest for as long as possible. </p><p>This strategy works fairly well if you combine it with a Builder, i.e.. Build a bunch of planets, but Bank on one of them. Harder to defend your empire this way. </p>
				<div class="faq-message"><h1>PROS</h1><div class="faq-content">You can make a metric buttload of cash if you're patient. </div></div>
				<div class="faq-message"><h1>CONS</h1><div class="faq-content">You can only play every couple of days and you don't have many planets to produce for you. Plus you run the risk of your bank becoming a juicy target</div></div>
            <h3>The Conqueror</h3>
				<p>The premise here is that you build up your military techs (shields, armor, computers, torps, cloak, and to a lesser degree sensors) and use them to take other people's planets. You then use the money acquired from these new planets to upgrade even further. You end up with lots of ill gotten colonists and planets this way. They will make money for you and you will gain an empire similar to that a Builder might create. </p><p>Be sure to stock every new planet acquired with plenty of fighters and torps to be sure that the former owner won't come and try to take the planet back. Trust me, that sucks. </p>
				<div class="faq-message"><h1>PROS</h1><div class="faq-content">You can get a whole lot of colonists using a small number of turns.</div></div>
				<div class="faq-message"><h1>CONS</h1><div class="faq-content">Everyone will hate you and it's sometimes hard to defend new "acquisitions".</div></div>
			<h3>The Newbie</h3>
				<p>This is a list of thing's you should avoid doing!</p> 
				<ul>
					<li>Repeating Scans..... It is a waste of turns. Don't scan ships or planets unless you actively plan on attacking them. For one, it's a waste of turns. Also, it alerts the people you scanned and you will become a Marked person!
					<li>Trading energy or organics.... Its a waste of credits! Ore and goods will give you the greatest returns. (Also trade energy collected for a little extra credits!)
					<li>...realspace move unless you can get somewhere in 1 or 2 turns. I've seen people use 50-100 turns to move from where they are to sol. You could probably move from sector to sector and find a special port using less turns. Plus you might find other planets or trading ports along the way. </li>
				</ul>
		<a id="misc"></a><hr class="dotted" /></hr>
		<h2>Misc</h2>
        	<h3>Cool Tips</h3>
            <p>Below are some cool little hints, tricks and tips for you to use in the game. Think of any more? send them in using the contact form!</p>
            <ul>
                <li>Before you attack a planet check to see if it is set to sell. If it 
                is, buy all the energy. The planet's beams and shields will be made 
                useless. </li>
                <li>If you need to go to a special port and don't particularly care 
                where you go afterwards, shop at good old sector 0. After you're done 
                wait around and let the update that runs every 6 minutes place you in a 
                random sector. You effectively get a free move. Of course, this only 
                works if you're hull is over the allowed federation space limit. I call 
                this the "sol bump". </li>
                <li>Whenever you buy an EWD, buy a warp editor to go along with it. That 
                way, if you get attacked you can easily create a link back to wherever 
                you were before. If being there is important that is. 
                </li>
                <li>When you decide to start populating a sector, its a good tip to delete all the warp links inside that sector. This will make it harder for people to find without using realspace. Another little trick is to go to the neighbouring sectors and edit the warp links to skip your sector, so if someones not paying attention, they could slip straight past your sector!
                </li>
                <li>Whenever creating a planet, try not to name it, people are more likely to pay attention and scan a named planet vs a planet with a generic name!</li>
                <li>A safe place for your ship, if your ship can no longer be parked in the SOL system, you keep getting kicked, sometimes it better to keep your ship floating in space in a sector with 5 planets, vs landing on a planet. This means anyone wanting to take out your ship will need to take out 3 of your planets, vs taking out just 1 of your planets!</li>
			</ul>
            <h3>The Planet</h3>
            <p>SO when you get your first planet, you need to decide what its purpose is going to be. Is it going to be a bank? or a trading planet? Knowing this will help you set up the percentages on the planet</p>
            <p>For example, if you where setting up a planet to safely park your ship on it, you might thik to yourself, the planet needs ample defences to defend against any attack, so you might want to set organics to 15%, energy to 25%, fighters to 30% and torpedoes to 30%. This will ensure that for each server tick you get more energy to power your beams, you get more fighters and torpedoes to defend your planet with.</p>
            <p>Once you have a base on your planet it can be tempting to populate your planet to 100% colonist limit, however this is a bad thing to do... firstly, a planet with 100% colonists produces 0 extra colonists. As an example imagine that you don't have 100 million peeps on a single planet, but 50 million on two planets. Now, each planet will make 25k peeps per turn and you'll get your 250k credits worth between the two planets. The moral is if you wish to gain more credits, dont populate your planet to its maxumin potential, instead populate it to a suggested 20% of the population limit. This will allow your planet room to grow, and give you more credits in the long term!</p> 
		<a id="terms"></a><hr class="dotted" /></hr>
		<h2>Glossary</h2>
			<ul>
                <li><span class="faq-hightlight-text">creds</span>- short for credits. 
                <li><span class="faq-hightlight-text">EWD</span>- this is short for emergency warp device. 
                <li><span class="faq-hightlight-text">EWB burn</span>- when a player attacks another player specifically to activate and EWD he is doing a "burn". 
                <li><span class="faq-hightlight-text">fits/torps</span>- short for fighters and torpedos. You see this abbreviation in the forums all the time. 
                <li><span class="faq-hightlight-text">M or B or T or Q</span>- M -> Million, B -> Billion, T -> Trillion, Q -> Quadrillion</li>
                <li><span class="faq-hightlight-text">rs move</span>- this is just short for realspace move. It means using your engines to move. 
                <li><span class="faq-hightlight-text">sol bump</span>- when you're above a certain level you automatically get kicked out of federation space. People call this a sol bump. </LI>
        	</ul>
		<hr class="dotted" /></hr>
<div class="page-back">
<?php
if (empty($username))
{
	echo "Click <a href='index.php" . $link . "'>here</a> to go back to the home page";
}
else
{
	echo "Click <a href='main.php" . $link . "'>here</a> to go back to the main menu";
}
?>
</div>
    <div class="footer">
       <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
        <div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2014 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
    </div>
</div>
