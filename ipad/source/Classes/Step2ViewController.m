//
//  Step2ViewController.m
//  Redigone

#import <QuartzCore/QuartzCore.h>
#import "Step2ViewController.h"
#import "RedigoneViewController.h"
#import "PointView.h"
#import "GlobalData.h"
#import "Reachability.h"
#import "Step3ViewController.h"

#define CONNECTION_ERROR_TITLE @"Cannot Connect to the Server" 
#define CONNECTION_ERROR_MESSAGE @"You must connect to a Wi-Fi\n or cellular data network\n to process the image." 
#define CONNECTION_ERROR_CANCEL_BUTTON @"OK"

#define NO_SELECTIONS_ERROR_TITLE @"No red eyes selected" 
#define NO_SELECTIONS_ERROR_MESSAGE @"You must make at least one\nselection to process the image." 
#define NO_SELECTIONS_ERROR_CANCEL_BUTTON @"OK"

@implementation Step2ViewController
@synthesize imageView;
@synthesize image;
@synthesize slider;
@synthesize tableView;
@synthesize internetReachable;
@synthesize internetActive;
@synthesize step3ViewController;


/*
 *	Configuration for number of sections in the table view
 */
- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
	return 1;
}


/*
 *	Retrieves the total number of rows in the current section (we only use one) in the table view
 */
- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
	GlobalData * globalData = [GlobalData sharedInstance];
	return [globalData.selectedPoints count];
}


/*
 *	Loads a cell in the table view with content
 */
- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
	GlobalData * globalData = [GlobalData sharedInstance];
	PointView * p = [globalData.selectedPoints objectAtIndex: indexPath.row];
	
	static NSString * cellIdentifier = @"SelectionCell";
	UITableViewCell *cell = [self.tableView dequeueReusableCellWithIdentifier:cellIdentifier];
	
	if (cell == nil) {
		cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:cellIdentifier] autorelease];
	}

  cell.textLabel.font = [UIFont boldSystemFontOfSize:14];
	cell.textLabel.text = [NSString stringWithFormat:@"Selection %i", p.pointName];
	cell.textLabel.textColor = [UIColor blackColor];
	cell.selectionStyle = UITableViewCellSelectionStyleBlue;
  
  if ([globalData.selectedPoints count] > 2) {
    [self.tableView flashScrollIndicators];
  }  
	return cell;
}


/*
 *	Removes a selection from the image when it's corresponding
 *	cell is deleted from the table view
 */
- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath: (NSIndexPath *) indexPath {
	GlobalData * globalData = [GlobalData sharedInstance];

	PointView * p = [globalData.selectedPoints objectAtIndex: indexPath.row];
	[p removeFromSuperview];
	
	[globalData.selectedPoints removeObjectAtIndex: indexPath.row];
	globalData.currentPointView = [globalData.selectedPoints count]-1;
	
	if ([globalData.selectedPoints count] == 0) {
		globalData.nextPointName = 1;
	} else {
		[self clearCurrentSelections];
		
		PointView * p = [globalData.selectedPoints objectAtIndex: globalData.currentPointView];
		p.selected = YES;
		[globalData.selectedPoints replaceObjectAtIndex:globalData.currentPointView withObject:p];
		[[globalData.selectedPoints objectAtIndex: globalData.currentPointView] setNeedsDisplay];	
	}
	[self.tableView reloadData];
}


/*
 *	Marks the selection in the image as selected when it's corresponding
 *	cell in the table view is selected
 */
- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *) newIndexPath {
	GlobalData * globalData = [GlobalData sharedInstance];
	globalData.currentPointView = newIndexPath.row;
	
	[self clearCurrentSelections];
	
	PointView * p = [globalData.selectedPoints objectAtIndex: globalData.currentPointView];
	p.selected = YES;
	[self.slider setValue: p.radius];
	[globalData.selectedPoints replaceObjectAtIndex:globalData.currentPointView withObject:p];
	[[globalData.selectedPoints objectAtIndex: globalData.currentPointView] setNeedsDisplay];
	
}


/*
 *	Reloads all data points in the image when a selection
 *	change notification is received
 */
- (void) receiveSelectionNotification:(NSNotification *) notification {
		GlobalData * globalData = [GlobalData sharedInstance];
		if ([[notification name] isEqualToString:@"SelectionNotification"]) {
			//NSLog(@"Successfully received the selection notification!");
			//fix to prevent invisible cells, is there any other way than this?
			[self.slider setValue:globalData.default_radius];
			[self.tableView setEditing:NO animated:YES];
			[self.tableView reloadData];
			[self.tableView setEditing:YES animated:YES];
		}
}


/*
 *	Called when network reachability is changed
 */
- (void) reachabilityChanged:(NSNotification *)notice {	
	// get Reachability instance from notification
	Reachability * curReach = [notice object];
	NetworkStatus networkStatus = [curReach currentReachabilityStatus];
	
	[self configureForNetworkStatus:networkStatus];
}


/*
 *	Sets the network status flags
 */
- (void)configureForNetworkStatus:(NetworkStatus)status {
	
	switch (status) {
			
		case NotReachable: {
			//NSLog(@"No Internet connection available. Step2");
			self.internetActive = NO;
			
			break;
		}
			
		case ReachableViaWiFi: {
			//NSLog(@"Internet connection via WIFI. Step2");
			self.internetActive = YES;
			
			break;
		}
			
		case ReachableViaWWAN: {
			//NSLog(@"Internet connection via WWAN. Step2");
			self.internetActive = YES;
			
			break;
		}
	}
}



/*
 *	Displays an error message if no Internet connection is available,
 *	otherwise proceeds to Step3
 */
- (IBAction)switchPage:(id)sender {
	GlobalData * globalData = [GlobalData sharedInstance];
	
	if (self.internetActive == NO) {
		UIAlertView *alert = [[[UIAlertView alloc] 
													initWithTitle:CONNECTION_ERROR_TITLE
													message:CONNECTION_ERROR_MESSAGE
													delegate:self 
													cancelButtonTitle:CONNECTION_ERROR_CANCEL_BUTTON
													otherButtonTitles: nil] autorelease];
		[alert show];		
	} else if ([globalData.selectedPoints count] < 1) {
		UIAlertView *alert = [[[UIAlertView alloc] 
													 initWithTitle:NO_SELECTIONS_ERROR_TITLE
													 message:NO_SELECTIONS_ERROR_MESSAGE
													 delegate:self 
													 cancelButtonTitle:NO_SELECTIONS_ERROR_CANCEL_BUTTON
													 otherButtonTitles: nil] autorelease];
		[alert show];		
	} else {
		self.step3ViewController = [[Step3ViewController alloc] initWithNibName:@"Step3ViewController" bundle:[NSBundle mainBundle]];
		[self.navigationController pushViewController:self.step3ViewController animated:YES];
	
	}
}


/*
 *	Updates and redraws the selection point when the radius
 *	slider changes value
 */
- (IBAction) sliderValueChanged:(UISlider *)sender {
	GlobalData * globalData = [GlobalData sharedInstance];
	globalData.default_radius = [sender value];

	if ([globalData.selectedPoints count] > 0) {
		//Set the new radius of the currently selected object
		[[globalData.selectedPoints objectAtIndex: globalData.currentPointView] setRadius:globalData.default_radius];
		
		//Create temp PointView
		PointView * p = [globalData.selectedPoints objectAtIndex: globalData.currentPointView];
		CGRect frame = p.frame;
		
		frame.size.width = globalData.default_radius*2;
		frame.size.height = globalData.default_radius*2;
		
		//Recalculate the frame origin matching the new radius
		frame.origin.x = p.frame.origin.x - ((frame.size.width - p.frame.size.width) / 2 );
		frame.origin.y = p.frame.origin.y - ((frame.size.height - p.frame.size.height) / 2);
		
		if(frame.origin.x > globalData.imageViewLowerRight.x-(globalData.default_radius*2)) {
			frame.origin.x = globalData.imageViewLowerRight.x-(globalData.default_radius*2);
		} else if (frame.origin.x < globalData.imageViewUpperLeft.x) {
			frame.origin.x = globalData.imageViewUpperLeft.x;
		}
		
		if (frame.origin.y > globalData.imageViewLowerRight.y-(globalData.default_radius*2)) {
			frame.origin.y = globalData.imageViewLowerRight.y-(globalData.default_radius*2);
		} else if (frame.origin.y < globalData.imageViewUpperLeft.y) {
			frame.origin.y = globalData.imageViewUpperLeft.y;
		}
		
		p.frame = frame;
		
		//Set the new frame size
		[[globalData.selectedPoints objectAtIndex: globalData.currentPointView] setFrame:p.frame];
		
		[[globalData.selectedPoints objectAtIndex: globalData.currentPointView] setNeedsDisplay];
	}
}  


/*
 *	The designated initializer.  Override if you create the controller programmatically and want to perform customization that is not appropriate for viewDidLoad.
 */
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil {
	if ((self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil])) {
		// Custom initialization
		
		if(image == nil) {
			image = [[UIImage alloc] init];
		}
	
	}
	return self;
}



/*
 * Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
 */
- (void)viewDidLoad {
	GlobalData * globalData = [GlobalData sharedInstance];
	
  
  //Position the th slider vertically
	self.slider.transform = CGAffineTransformRotate(self.slider.transform, 270.0/180*M_PI);
  
	//Create image bounds
	CGSize imageViewBounds = CGSizeMake(globalData.adaptiveSizeX, globalData.adaptiveSizeY);

	
	CGFloat original_width = self.image.size.width;
	CGFloat original_height = self.image.size.height;
	
	//Resize image to fit within bounds if larger than bounds
  //Quick fix for bug that resized small images to larger images
  //The blocks below can be re-written more efficiently
  if (original_width > globalData.adaptiveSizeX || original_height > globalData.adaptiveSizeY) {
    
    self.image = [self.image resizedImageWithContentMode: UIViewContentModeScaleAspectFit
                            bounds: imageViewBounds
                    interpolationQuality: kCGInterpolationDefault];	
	}
  
	//Allocate imageview
	CGSize imageSize = [self.image size];
	self.imageView = [[UIImageView alloc] initWithFrame: CGRectMake(0, 0, imageSize.width, imageSize.height)];
	self.imageView.userInteractionEnabled = NO;
	self.imageView.hidden = NO;
	self.imageView.tag = 99;
	self.imageView.image = self.image;
	
	[self.image release];	
	
	CGPoint tmpPoint = CGPointMake(65, 164);
	CGRect tmpBounds = self.imageView.bounds;
	
	self.imageView.frame = CGRectMake(tmpPoint.x, tmpPoint.y, tmpBounds.size.width, tmpBounds.size.height);	
	
	
	[self.imageView.layer setBorderColor: [[UIColor whiteColor] CGColor]];
	[self.imageView.layer setBorderWidth: 2.0];

	//Add view to current view
	[self.view addSubview: self.imageView];
	[self.view bringSubviewToFront: self.imageView];
	
	//Reposition the view
	CGPoint newPoint = CGPointMake(globalData.imagePositionX, globalData.imagePositionY);
	self.imageView.center = newPoint;

	//Store imageView frame coordinates in globalData
	globalData.imageViewUpperLeft = CGPointMake(self.imageView.center.x - (self.imageView.frame.size.width / 2), self.imageView.center.y - (self.imageView.frame.size.height / 2));
	//NSLog(@"imageViewUpperLeft: %.1f, %.1f", globalData.imageViewUpperLeft.x, globalData.imageViewUpperLeft.y);
	globalData.imageViewLowerRight = CGPointMake(self.imageView.center.x + (self.imageView.frame.size.width / 2), self.imageView.center.y + (self.imageView.frame.size.height / 2));
	//NSLog(@"imageViewLowerRight: %.1f, %.1f", globalData.imageViewLowerRight.x, globalData.imageViewLowerRight.y);		
	
	
	//Now we can calculate the ratio of the resize image to the original image
	globalData.ratio = original_width / imageSize.width;
	globalData.original_width = original_width;
	globalData.original_height = original_height;
	

	//NSLog(@"image width 1: %.1f", imageSize.width);
	//NSLog(@"image width 2: %.1f", original_width);
	//NSLog(@"image height 1: %.1f", imageSize.height);
	//NSLog(@"image height 2: %.1f", original_height);
	//NSLog(@"ratio %.3f", globalData.ratio);
	
	//CGSize imageSize = [self.imageView.image size];
	//NSLog(@"Size of loaded image: %.3f x %.3f", imageSize.width, imageSize.height);
	
	/*
	CGRect titleRect = CGRectMake(0, 0, 300, 60);
	UILabel *tableTitle = [[UILabel alloc] initWithFrame:titleRect];
	
	tableTitle.textColor = [UIColor whiteColor];
	tableTitle.backgroundColor = [self.tableView backgroundColor];
	tableTitle.textAlignment = UITextAlignmentCenter;
	tableTitle.opaque = YES;
	tableTitle.font = [UIFont boldSystemFontOfSize:25];
	//tableTitle.text = @"Selections";
	
	self.tableView.tableHeaderView = tableTitle;
	[self.tableView reloadData];
	[tableTitle release];
   */
	
	//Setup the notification center for selections
	[[NSNotificationCenter defaultCenter] addObserver:self
																				selector:@selector(receiveSelectionNotification:) 
																				name:@"SelectionNotification"
																				object:nil];
	
	//Notification center for internet connection
	[[NSNotificationCenter defaultCenter] addObserver:self
																					 selector:@selector(reachabilityChanged:)
																							 name:kReachabilityChangedNotification
																						 object:nil];
	
	
	self.internetReachable = [[Reachability reachabilityForInternetConnection] retain];
	//self.internetReachable = [[Reachability reachabilityWithHostName:@"api.redigone.com"] retain];

	// get first status
	NetworkStatus siteNetworkStatus = [self.internetReachable currentReachabilityStatus];
	
	[self configureForNetworkStatus:siteNetworkStatus];
	[self.internetReachable startNotifier];	
	
	[super viewDidLoad];
}


/*
 * Clear all selection points
 */
- (void)clearCurrentSelections {
	GlobalData * globalData = [GlobalData sharedInstance];
	
	PointView * p;
	for (int i=0; i < [globalData.selectedPoints count]; i++) {
		p = [globalData.selectedPoints objectAtIndex: i];
		p.selected = NO;
		
		[globalData.selectedPoints replaceObjectAtIndex:i withObject:p];
		[[globalData.selectedPoints objectAtIndex: i] setNeedsDisplay];
	}
}


/*
 * Called when a user touch has ended
 */
- (void)touchesEnded:(NSSet *)touches withEvent:(UIEvent *)event {

	GlobalData * globalData = [GlobalData sharedInstance];
	if([globalData.selectedPoints count] < globalData.maxPoints) {
	
		//Get all the touches.
		NSSet *allTouches = [event allTouches];
		
		//Number of touches on the screen
		switch ([allTouches count]) {
			case 1: {
				//Get the first touch.
				UITouch *touch = [[allTouches allObjects] objectAtIndex:0];
				
				CGPoint position = [touch locationInView:touch.view];
				//NSLog(@"Touch point: %.1f, %.1f", position.x, position.y);
				
				//Calculate the upper left coordinate of the loaded image
				CGPoint imageViewUpperLeft = CGPointMake(self.imageView.center.x - (self.imageView.frame.size.width / 2), self.imageView.center.y - (self.imageView.frame.size.height / 2));
				//NSLog(@"imageViewUpperLeft: %.1f, %.1f", imageViewUpperLeft.x, imageViewUpperLeft.y);
				
				//Calculate the lower right coordinate of the loaded image
				CGPoint imageViewLowerRight = CGPointMake(self.imageView.center.x + (self.imageView.frame.size.width / 2), self.imageView.center.y + (self.imageView.frame.size.height / 2));
				//NSLog(@"imageViewLowerRight: %.1f, %.1f", imageViewLowerRight.x, imageViewLowerRight.y);
				
				//Check if touch was within the boundaries of the loaded image and draw selection if so
				if ((position.x >= imageViewUpperLeft.x && position.x <= imageViewLowerRight.x) && (position.y >= imageViewUpperLeft.y && position.y <= imageViewLowerRight.y)) {
					
					[self clearCurrentSelections];
					
					//Create the frame for the PointView
					//only done if no PointView exists at that location
					CGRect pointRect = CGRectMake(position.x - globalData.default_radius, position.y - globalData.default_radius, globalData.default_radius*2, globalData.default_radius*2);

					//If necessary, reposition origin to make sure the rectangle boundaries are within the image frame
					if(pointRect.origin.x > imageViewLowerRight.x-(globalData.default_radius*2)) {
						pointRect.origin.x = imageViewLowerRight.x-(globalData.default_radius*2);
					} else if (pointRect.origin.x < imageViewUpperLeft.x) {
						pointRect.origin.x = imageViewUpperLeft.x;
					}
					
					if (pointRect.origin.y > imageViewLowerRight.y-(globalData.default_radius*2)) {
						pointRect.origin.y = imageViewLowerRight.y-(globalData.default_radius*2);
					} else if (pointRect.origin.y < imageViewUpperLeft.y) {
						pointRect.origin.y = imageViewUpperLeft.y;
					}					
					
					PointView *pv = [[PointView alloc] initWithFrame:pointRect];
					[pv setUserInteractionEnabled:YES];

					[globalData.selectedPoints addObject: pv];

          //sort points in descending order
          //http://esscomp.co.uk/how-to-sort-an-nsmutablearray-using-nssortdes
          
          NSSortDescriptor *sortDescriptor = [[[NSSortDescriptor alloc] initWithKey:@"pointName" ascending:NO] autorelease];
          NSArray *sortDescriptors = [NSArray arrayWithObject:sortDescriptor];
          NSArray *sortedArray = [globalData.selectedPoints sortedArrayUsingDescriptors:sortDescriptors];
          
          //remove all existing objects
          [globalData.selectedPoints removeAllObjects];
          
          //add them back sorted
          [globalData.selectedPoints addObjectsFromArray: sortedArray];
          
          //end sort					
          
					[super.view addSubview:pv];
					[pv release];				
				}
				
				[self.tableView setEditing:NO animated:YES];
				[self.tableView reloadData];
				[self.tableView setEditing:YES animated:YES];
			}
				break;
		}
	}
	
	[super touchesEnded: touches withEvent:event];
	
}


/*
 * Go back to Step1
 */
- (IBAction)startOver:(id)sender {
	GlobalData * globalData = [GlobalData sharedInstance];
	[globalData resetData];
	
	[super.navigationController popToRootViewControllerAnimated:YES];
}


/*
 *	Override to allow orientations other than the default portrait orientation.
 */
- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
	if ((interfaceOrientation==UIInterfaceOrientationPortrait)||(interfaceOrientation==UIInterfaceOrientationPortraitUpsideDown)) {
		return NO;
	}
	
	if ((interfaceOrientation==UIInterfaceOrientationLandscapeLeft)||(interfaceOrientation==UIInterfaceOrientationLandscapeRight)) {
		return YES;
	}
	
	return NO;
}


- (void)didReceiveMemoryWarning {
	// Releases the view if it doesn't have a superview.
	[super didReceiveMemoryWarning];
	// Release any cached data, images, etc that aren't in use.
}

- (void) viewWillDisappear:(BOOL)animated {
	[[NSNotificationCenter defaultCenter] removeObserver:self];
	[self.internetReachable release];
}


- (void)viewDidUnload {
	[super viewDidUnload];
	// Release any retained subviews of the main view.
	self.image = nil;
	self.imageView = nil;
	self.slider = nil;
	self.tableView = nil;
	self.internetActive = NO;
}


- (void)dealloc {
	[tableView release];
	[imageView release];
	[image release];
	[slider release];
	[super dealloc];
}

@end
