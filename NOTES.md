Fields to add:

BILAG Form:

| Variable | Label | Eqn                                                                             |
| -------- | ----- |---------------------------------------------------------------------------------|
| bilag_const_prev | Bilag Constitutional Previous | if([event-number]=1, 0, if([previous-event-name][bilag_const2004] = "E", 0, 1)) |
| bilag_muco_prev | Bilag Muco Previous | if([event-number]=1, 0, if([previous-event-name][bilag_muco2004] = "E", 0, 1))  |


Questions for Carla:
- Bilag Form
  - Is there a reason that we are showing 0,1, 2, 3, 4 rather than "Not present", Improving, Same, Worse?