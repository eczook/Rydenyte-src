<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/config.php"; 
require_once $_SERVER["DOCUMENT_ROOT"]."/core/auth.php";

$stmt = $db->prepare("SELECT * FROM games WHERE is_cool = 1");
$stmt->execute();
$coolGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/meta.php"; ?>

<title>RYDENYTE: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
<div id="Container">
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/header.php"; ?>
	<script type="text/javascript">
	(function () {

		var speed = 500;
		var timerInterval = 10;

		function addEvent(el, ev, fn) {
			if (el.attachEvent) {
				el.attachEvent('on' + ev, fn);
			} else {
				el.addEventListener(ev, fn, false);
			}
		}

		function hasClass(el, className) {
			return (' ' + el.className + ' ').indexOf(' ' + className + ' ') > -1;
		}

		function getHeaders() {
			var all = document.getElementsByTagName('*');
			var headers = [];
			var i;

			for (i = 0; i < all.length; i++) {
				if (
					hasClass(all[i], 'TopAccordionHeader') ||
					hasClass(all[i], 'AccordionHeader') ||
					hasClass(all[i], 'BottomAccordionHeader')
				) {
					headers.push(all[i]);
				}
			}

			return headers;
		}

		function getContent(header) {
			var parentDiv = header.parentNode;
			var nextElement = parentDiv.nextSibling;

			while (nextElement && nextElement.nodeType != 1) {
				nextElement = nextElement.nextSibling;
			}

			if (nextElement && nextElement.tagName.toUpperCase() == 'DIV') {
				return nextElement;
			}

			return null;
		}

		function setNaturalHeight(el) {
			el.style.overflow = "";
			el.style.height = "auto";
			el.style.display = "block";
		}

		function getHeight(el) {
			return el.scrollHeight;
		}

		function easeSine(progress) {
			return (-Math.cos(progress * Math.PI) / 2) + 0.5;
		}

		function slideDown(el) {

			if (el.style.display == "block") {
				return;
			}

			el.style.display = "block";
			el.style.overflow = "hidden";
			el.style.height = "0px";

			var fullHeight = getHeight(el);
			var startTime = new Date().getTime();

			var timer = setInterval(function () {

				var currentTime = new Date().getTime();
				var elapsed = currentTime - startTime;

				var progress = elapsed / speed;

				if (progress > 1) {
					progress = 1;
				}

				var eased = easeSine(progress);

				el.style.height = Math.round(fullHeight * eased) + "px";

				if (progress >= 1) {
					clearInterval(timer);
					el.style.height = "auto";
					el.style.overflow = "";
				}

			}, timerInterval);
		}

		function slideUp(el) {

			if (el.style.display == "none") {
				return;
			}

			var fullHeight = el.offsetHeight;

			el.style.overflow = "hidden";

			var startTime = new Date().getTime();

			var timer = setInterval(function () {

				var currentTime = new Date().getTime();
				var elapsed = currentTime - startTime;

				var progress = elapsed / speed;

				if (progress > 1) {
					progress = 1;
				}

				var eased = easeSine(progress);

				el.style.height = Math.round(fullHeight * (1 - eased)) + "px";

				if (progress >= 1) {
					clearInterval(timer);
					el.style.height = "0px";
					el.style.display = "none";
				}

			}, timerInterval);
		}

		function init() {

			var headers = getHeaders();
			var i;

			for (i = 0; i < headers.length; i++) {

				(function (header) {

					var content = getContent(header);

					if (!content) {
						return;
					}

					if (content.style.display == "none") {
						content.style.height = "0px";
					} else {
						setNaturalHeight(content);
					}

					addEvent(header, "click", function () {

						if (content.style.display == "none") {
							slideDown(content);
						} else {
							slideUp(content);
						}

					});

				})(headers[i]);
			}
		}

		addEvent(window, "load", init);

	})();
	</script>
    <div id="Body">
        <div id="BadgesContainer">
		<div id="ctl00_cphRydenyte_aBadgesAndRankings">
	<input type="hidden" name="ctl00$cphRydenyte$aBadgesAndRankings_AccordionExtender_ClientState" id="ctl00_cphRydenyte_aBadgesAndRankings_AccordionExtender_ClientState" value="0"><div>
		<h4 class="TopAccordionHeader">Community Badges</h4>
	</div><div style="display: block; height: auto;">
		
						<div id="CommunityBadges">
							<div class="Legend">
								<ul class="BadgesList">
									<li id="Administrator">
										<h4>Administrator Badge</h4>
										<div>This badge identifies an account as belonging to a Rydenyte administrator. Only official Rydenyte administrators will possess this badge. If someone claims to be an admin, but does not have this badge, they are potentially trying to mislead you. If this happens, please report abuse and we will delete the imposter's account.</div>
									</li>
									<li id="ForumModerator">
										<h4>Forum Moderator Badge</h4>
										<div>Users with this badge are forum moderators. They have special powers on the Rydenyte forum and are able to delete threads that violate the Community Guidelines. Users who are exemplary citizens on Rydenyte over a long period of time may be invited to be moderators. This badge is granted by invitation only.</div>
									</li>
									<li id="ImageModerator">
										<h4>Image Moderator Badge</h4>
										<div>Users with this badge are image moderators. Image moderators have special powers on Rydenyte that allow them to approve or disapprove images that other users upload. Rejected images are immediately banished from the site. Users who are exemplary citizens on Rydenyte over a long period of time may be invited to be moderators. This badge is granted by invitation only.</div>
									</li>
									<li id="Verified">
										<h4>Verified Badge</h4>
										<div>Users with this badge have verified their discord and proved to the moderators that they are legit. try to verify with another account and you'll get PWNED.</div>
									</li>
								</ul>
							</div>
							<div id="FeaturedBadge_Community">
								<h4>Builders Club</h4>
								<div class="FeaturedBadgeContent">
									<div class="FeaturedBadgeIcon"><img id="ctl00_cphRydenyte_ctl01_iFeaturedBadge_Community" src="/images/Badges/BuildersClub-125x125.png" height="125" width="125" border="0"></div>
									<p>Members of the illustrious Builders Club display this badge proudly. The Builders Club is a paid premium service. Members receive several benefits: they get ten places on their account instead of one, they earn a daily income of 15 RYBUX, they can sell their creations to others in the Rydenyte Catalog, they get the ability to browse the web site without external ads, and they receive the exclusive Builders Club construction hat.</p>
								</div>
							</div>
							<div style="clear:both;"></div>
						</div>
					
	</div><div>
		<h4 class="AccordionHeader">Builder Badges</h4>
	</div><div style="display: none; height: 0px;">
		
						<div id="VisitsBadges">
							<div class="Legend">
								<ul class="BadgesList">
									<li id="Homestead">
										<h4>Homestead Badge</h4>
										<div>The homestead badge is earned by having your personal place visited 100 times. Players who achieve this have demonstrated their ability to build cool things that other Rydenyians were interested enough in to check out. Get a jump-start on earning this reward by inviting people to come visit your place.</div>
									</li>
									<li id="Bricksmith">
										<h4>Bricksmith Badge</h4>
										<div>The Bricksmith badge is earned by having a popular personal place. Once your place has been visited 1000 times, you will receive this award. Rydenyians with Bricksmith badges are accomplished builders who were able to create a place that people wanted to explore a thousand times. They no doubt know a thing or two about putting bricks together.</div>
									</li>
								</ul>
							</div>
							<div id="StatisticsRankingsPane_Visits">
								
							</div>
							<div style="clear:both;"></div>
						</div>
					
	</div><div>
		<h4 class="AccordionHeader">Friendship Badges</h4>
	</div><div style="display: none; height: 0px;">
		
						<div id="FriendshipBadges">
							<div class="Legend">
								<ul class="BadgesList">
									<li id="Friendship">
										<h4>Friendship Badge</h4>
										<div>This badge is given to players who have embraced the Rydenyte community and have made at least 20 friends. People who have this badge are good people to know and can probably help you out if you are having trouble.</div>
									</li>
									<li id="Inviter">
										<h4>Inviter Badge</h4>
										<div>Rydenyia is a vast uncharted realm, as large as the imagination. Individuals who invite others to join in the effort of mapping this mysterious region are honored in Rydenyian society. Citizens who successfully recruit three or more fellow explorers via the Share Rydenyte with a Friend mechanism are awarded with this badge.</div>
									</li>
								</ul>
							</div>
							<div id="StatisticsRankingsPane_Friendship">
								
							</div>
							<div style="clear:both;"></div>
						</div>
					
	</div><div>
		<h4 class="BottomAccordionHeader">Combat Badges</h4>
	</div><div style="display: none; height: 0px;">
		
						<div id="CombatBadges">
							<div class="Legend">
								<ul class="BadgesList">
									<li id="CombatInitiation">
										<h4>Combat Initiation Badge</h4>
										<div>This badge is given to any player who has proven his or her combat abilities by accumulating 10 victories in battle. Players who have this badge are not complete newbies and probably know how to handle their weapons.</div>
									</li>
									<li id="Warrior">
										<h4>Warrior Badge</h4>
										<div>This badge is given to the warriors of Rydenyia, who have time and time again overwhelmed their foes in battle. To earn this badge, you must rack up 100 knockouts. Anyone with this badge knows what to do in a fight!</div>
									</li>
									<li id="Bloxxer">
										<h4>Bloxxer Badge</h4>
										<div>Anyone who has earned this badge is a very dangerous player indeed. Those Rydenyians who excel at combat can one day hope to achieve this honor, the Bloxxer Badge. It is given to the warrior who has bloxxed at least 250 enemies and who has tasted victory more times than he or she has suffered defeat. Salute!</div>
									</li>
								</ul>
							</div>
							<div id="StatisticsRankingsPane_Combat">
								
							</div>
							<div style="clear:both;"></div>
						</div>
					
	</div>
</div>
	</div>
    </div>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/core/templates/footer.php"; ?>
</div>