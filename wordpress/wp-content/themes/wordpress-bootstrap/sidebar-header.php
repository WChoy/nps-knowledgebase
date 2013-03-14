				<div id="sidebar-header" class="fluid-sidebar container-fluid" role="complementary">
				
					<?php if ( is_active_sidebar( 'header' ) ) : ?>

						<?php dynamic_sidebar( 'header' ); ?>

					<?php else : ?>

						<!-- This content shows up if there are no widgets defined in the backend. -->
						
						<div class="alert alert-message">
						
							<p><?php _e("Please activate some Widgets","bonestheme"); ?>.</p>
						
						</div>

					<?php endif; ?>

				</div>