//
//  PointView.m
//  Redigone


#import "PointView.h"
#import "GlobalData.h"

@implementation PointView
@synthesize startLocation;
@synthesize radius;
@synthesize pointName;
@synthesize selected;

- (id)initWithFrame:(CGRect)frame {
		GlobalData * globalData = [GlobalData sharedInstance];
    if ((self = [super initWithFrame:frame])) {
			//NSLog(@"Frame size: %.3f, %.3f", frame.size.width, frame.size.height);
			//NSLog(@"Frame origin: %.3f, %.3f", frame.origin.x, frame.origin.y);
			// Initialization code
			self.backgroundColor = UIColor.clearColor;
			self.radius = globalData.default_radius;
			self.selected = YES;
			
			//NSLog(@"globalData nextPointName: %i", globalData.nextPointName);
			self.pointName = globalData.nextPointName;
			globalData.nextPointName++;
			globalData.currentPointView = [globalData.selectedPoints count];
		}
    return self;
}

- (void)touchesBegan:(NSSet *)touches withEvent:(UIEvent *)event {
		GlobalData * globalData = [GlobalData sharedInstance];

		PointView * p;
		for (int i=0; i < [globalData.selectedPoints count]; i++) {
			p = [globalData.selectedPoints objectAtIndex: i];
			if (p.pointName == self.pointName) {
				globalData.currentPointView = i;
				p.selected = YES;
			} else {
				p.selected = NO;
			}
			
			[globalData.selectedPoints replaceObjectAtIndex:i withObject:p];
			[[globalData.selectedPoints objectAtIndex: i] setNeedsDisplay];
		}
	
		globalData.default_radius = self.radius;
		CGPoint pt = [[touches anyObject] locationInView:self];
		self.startLocation = pt;
		[[self superview] bringSubviewToFront:self];
		
		//Notify all instances of Step2ViewController
		[[NSNotificationCenter defaultCenter] postNotificationName:@"SelectionNotification" object:self];	
}


- (void) touchesMoved:(NSSet *)touches withEvent:(UIEvent *)event {
	GlobalData * globalData = [GlobalData sharedInstance];
	
	CGPoint pt = [[touches anyObject] locationInView:self];
	CGRect frame = [self frame];
	
	frame.origin.x += pt.x - self.startLocation.x;
	frame.origin.y += pt.y - self.startLocation.y;

	//If necessary, reposition origin to make sure the rectangle boundaries are within the image frame
	if(frame.origin.x > globalData.imageViewLowerRight.x-(self.radius*2)) {
		frame.origin.x = globalData.imageViewLowerRight.x-(self.radius*2);
	} else if (frame.origin.x < globalData.imageViewUpperLeft.x) {
		frame.origin.x = globalData.imageViewUpperLeft.x;
	}
	
	if (frame.origin.y > globalData.imageViewLowerRight.y-(self.radius*2)) {
		frame.origin.y = globalData.imageViewLowerRight.y-(self.radius*2);
	} else if (frame.origin.y < globalData.imageViewUpperLeft.y) {
		frame.origin.y = globalData.imageViewUpperLeft.y;
	}
	
	[self setFrame:frame];
}

// Only override drawRect: if you perform custom drawing.
// An empty implementation adversely affects performance during animation.
- (void)drawRect:(CGRect)rect {	
	// Drawing code
	//GlobalData * globalData = [GlobalData sharedInstance];
	CGContextRef contextRef = UIGraphicsGetCurrentContext();
	if (self.selected == YES) {
		CGContextSetRGBStrokeColor(contextRef, 0.007, 0.466, 0.941, 1.0);
	} else {
		CGContextSetRGBStrokeColor(contextRef, 1.0, 1.0, 1.0, 1.0);
	}
	//NSLog(@"self.frame.origin.x: %.3f", self.frame.origin.x);
	//NSLog(@"self.frame.origin.y: %.3f", self.frame.origin.y);
	
	//NSLog(@"self.radius: %.3f", self.radius);
	//NSLog(@"self.radius-2 *2 %.3f", (self.radius-2)*2);
	CGContextStrokeEllipseInRect(contextRef, CGRectMake(1, 1, (self.radius-1)*2, (self.radius-1)*2));
	//self.backgroundColor = UIColor.clearColor;
}


- (void)dealloc {
    [super dealloc];
}


@end
