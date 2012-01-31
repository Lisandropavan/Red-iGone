//
//  GlobalData.h
//  Redigone

//How to use
//At the top: #import "GlobalData.h"
//In methods: GlobalData *globalData = [GlobalData sharedInstance];

#import <Foundation/Foundation.h>


@interface GlobalData : NSObject {
	//The NSMutableArray class declares the programmatic interface to
	//objects that manage a modifiable array of objects.
	//This class adds insertion and deletion operations to the basic
	//array-handling behavior inherited from NSArray.
	NSMutableArray * selectedPoints;
	int maxPoints;
	CGFloat default_radius;
	CGPoint imageViewUpperLeft;
	CGPoint imageViewLowerRight;
	int nextPointName;
	int currentPointView;
	UIImage * image;
	CGFloat ratio;
	CGFloat original_width;
	CGFloat original_height;
	NSURL * image_url;
	NSMutableArray * thumbImages;
	NSString * adaptiveResize;
	NSString * extension;
	CGFloat adaptiveSizeX;
	CGFloat adaptiveSizeY;
	CGFloat imagePositionX;
	CGFloat imagePositionY;
	BOOL	unrecoverableError;
	int statusCode;
	int saveStatusCode;
  BOOL twitpicUpload;
	
	//XML Vars
	NSString * queue_id;
	NSString * queue_th;
	NSString * queue_name;
	NSString * original_name;
	NSString * original_thumb_name;
	NSString * processed_image_name;
	NSString * processed_image_th;
	
	NSURL * current_download_url;
	NSURL * base_url;
  NSURL * secure_base_url;
	
  //Twitpic
  BOOL * twitpicReponse;
  NSString * twitpicResponse;
	
	//About UIAlertView
	NSString * about_title;
	NSString * about_messsage;
	NSString * about_cancel_button_title;
  
  //API Key and Session ID
  NSString * api_key;
  NSString * session_id;
  int num_retries;
  NSString * current_function;
  
  NSDate * image_expiration;
}

@property (nonatomic, retain) NSMutableArray * selectedPoints;
@property int maxPoints;
@property CGFloat default_radius;
@property CGPoint imageViewUpperLeft;
@property CGPoint imageViewLowerRight;
@property int nextPointName;
@property int currentPointView;
@property (nonatomic, retain) UIImage * image;
@property CGFloat ratio;
@property CGFloat original_width;
@property CGFloat original_height;
@property (nonatomic, retain) NSURL * image_url;
@property (nonatomic, retain) NSMutableArray * thumbImages;
@property (nonatomic, retain) NSString * adaptiveResize;
@property (nonatomic, retain) NSString * extension;
@property CGFloat adaptiveSizeX;
@property CGFloat adaptiveSizeY;
@property CGFloat imagePositionX;
@property CGFloat imagePositionY;
@property BOOL unrecoverableError;
@property int statusCode;
@property int saveStatusCode;

@property BOOL twitpicUpload;
@property (nonatomic, retain) NSString * twitpicResponse;

@property (nonatomic, retain) NSString * queue_id;
@property (nonatomic, retain) NSString * queue_th;
@property (nonatomic, retain) NSString * queue_name;
@property (nonatomic, retain) NSString * original_name;
@property (nonatomic, retain) NSString * original_thumb_name;
@property (nonatomic, retain) NSString * processed_image_name;
@property (nonatomic, retain) NSString * processed_image_th;

@property (nonatomic, retain) NSURL * current_download_url;
@property (nonatomic, retain) NSURL * base_url;
@property (nonatomic, retain) NSURL * secure_base_url;

@property (nonatomic, retain) NSString * about_title;
@property (nonatomic, retain) NSString * about_messsage;
@property (nonatomic, retain) NSString * about_cancel_button_title;

@property (nonatomic, retain) NSString * session_id;
@property (nonatomic, retain) NSString * api_key;
@property int num_retries;
@property (nonatomic, retain) NSString * current_function;

@property (nonatomic, retain) NSDate * image_expiration;

+ (GlobalData *) sharedInstance;
- (void) resetData;

@end
